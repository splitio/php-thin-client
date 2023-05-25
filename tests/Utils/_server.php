<?php

require_once dirname(__FILE__) . "/../../vendor/autoload.php";

use \SplitIO\ThinClient\Link\Transfer\Framing\LengthPrefix;
use \SplitIO\ThinClient\Link\Transfer\Framing\Framer;
use \SplitIO\Test\Utils\SocketServerRemoteControl;


class SocketCloseRequested extends \Exception
{
}

class NoOpFramingWrapper implements Framer
{

    const BUF_SIZE = 8 * 1024 * 1024;

    function SendFrame(/*\Socket */$sock, string $message): int
    {
        return socket_send($sock, $message, strlen($message), 0);
    }

    function ReadFrame(/*\Socket */$sock, string &$buffer): int
    {
        return socket_recv($sock, $buffer, self::BUF_SIZE, 0);
    }
}

class SocketServer
{
    private $interactions;
    private $socket;
    private $framingWrapper;
    private $connectionsToAccept;
    private $needsExtraBufferSpace;
    private $parentPid;

    public function __construct($input)
    {

        fwrite(STDERR, "SERVER -- CONSTRUCTING\n");

        $setup = $input["setup"];

        $this->parentPid = $setup['parentPid'];
        $this->interactions = $input["interactions"];
        $this->connectionsToAccept = $setup["connectionsToAccept"] ?? 1;
        switch ($setup["socketType"]) {
            case SocketServerRemoteControl::UNIX_STREAM:
                $this->framingWrapper = new LengthPrefix();
                $addressFamily = AF_UNIX;
                $socketType = SOCK_STREAM;
                break;
            case SocketServerRemoteControl::UNIX_SEQPACKET:
                $this->needsExtraBufferSpace = true;
                $this->framingWrapper = new NoOpFramingWrapper();
                $addressFamily = AF_UNIX;
                $socketType = SOCK_SEQPACKET;
                break;
            default:
                throw new \Exception("unknown socket type: " . $setup['socketType']);
        }

        if (($this->socket = @socket_create($addressFamily, $socketType, 0)) === false) {
            throw new \Exception("socket_create() failed: reason: " . socket_strerror(socket_last_error()) . "\n");
        }

        if (@socket_bind($this->socket, $input["setup"]["socketAddress"]) === false) {
            throw new \Exception("socket_bind() failed: reason: " . socket_strerror(socket_last_error($this->socket)) . "\n");
        }

        if ((@socket_listen($this->socket, 10)) === false) {
            throw new \Exception("socket_listen() failed: reason: " . socket_strerror(socket_last_error($this->socket)) . "\n");
        }
    }

    public function run(): void
    {

        fwrite(STDERR, "SERVER -- STARTING \n");

        posix_kill($this->parentPid, SIGUSR1);

        fwrite(STDERR, "SERVER -- SIGUSR1 SENT \n");

        while ($this->connectionsToAccept-- > 0) {
            $clientSock = null;
            try {

                if (($clientSock = @socket_accept($this->socket)) === false) {
                    throw new \Exception("socket_accept() failed: reason: "
                        . socket_strerror(socket_last_error($this->socket)) . "\n");
                }

                // SEQPACKET sockets hava datagram-like messages boundaries & need bigger buffers for larger transfers.
                if ($this->needsExtraBufferSpace) {
                    if (@socket_set_option($clientSock, SOL_SOCKET, SO_SNDBUF, NoOpFramingWrapper::BUF_SIZE) == false) {
                        throw new \Exception("error updating socket buffer: " . socket_strerror(socket_last_error($this->socket)));
                    }
                    if (@socket_set_option($clientSock, SOL_SOCKET, SO_RCVBUF, NoOpFramingWrapper::BUF_SIZE) == false) {
                        throw new \Exception("error updating socket buffer: " . socket_strerror(socket_last_error($this->socket)));
                    }
                }

                foreach ($this->interactions as $testCase) {
                    $this->handleTestCase($testCase, $clientSock);
                }

            } catch (SocketCloseRequested $exc) {
                ($exc); // do nothing without complaining about an unused variable
            } finally {
                @socket_shutdown($clientSock);
                @socket_close($clientSock);
            }
        }
    }

    private function handleTestCase(array $testCase, $clientSock)
    {
        try {
            if (isset($testCase['actions_pre'])) {
                $this->handleAction($testCase['actions_pre']);
            }

            if (isset($testCase['expects'])) {
                $buf = "";
                $this->framingWrapper->ReadFrame($clientSock, $buf);
                if ($buf != base64_decode($testCase['expects'])) {
                    throw new \Exception("incoming value mismatch. Expected='"
                        . base64_decode($testCase['expects']) . "' / Actual='" . $buf . "'");
                }
            }

            if (isset($testCase['actions_during'])) {
                $this->handleAction($testCase['actions_during']);
            }

            if (isset($testCase['returns'])) {
                if (false === $this->framingWrapper->SendFrame($clientSock, base64_decode($testCase["returns"]))) {
                    throw new \Exception("failed to send 'return' value via socket: "
                        . socket_strerror(socket_last_error($clientSock)) . "\n");
                }
            }
        } finally {
            posix_kill($this->parentPid, SIGUSR2);
        }
    }

    private function handleAction($action)
    {
        switch ($action['type'] ?? 'none') {
            case 'break':
                throw new \SocketCloseRequested();
            case 'delay':
                usleep($action['us']);
                break;
            case 'none':
            default:
        }
    }

    public function shutdown(): void
    {
        @socket_close($this->socket);
    }
}

// Main execution flow

fwrite(STDERR, "SERVER PID: " . posix_getpid() . "\n");
fwrite(STDERR, "PARENT PID: " . posix_getppid() . "\n");

fwrite(STDERR, "SERVER -- READING STDIN\n");
$contents = "";
while (!feof(STDIN)) {
    $contents .= fread(STDIN, 9999999);
}
fwrite(STDERR, "SERVER -- CLOSING STDIN\n");
fclose(STDIN);
fwrite(STDERR, "SERVER -- STDIN CLOSED\n");

$input = json_decode(trim($contents), true);
$server = null;
$exitCode = 0;
try {
    $server = new SocketServer($input);
    $server->run();
} catch (\Exception $exc) {
    $exitCode = 1;
    fwrite(STDERR, $exc);
} finally {
    $server->shutdown();
}

exit($exitCode);
