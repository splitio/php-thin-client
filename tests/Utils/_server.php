<?php

require_once dirname(__FILE__) . "/../../vendor/autoload.php";

use \SplitIO\ThinClient\Link\Transfer\Framing\LengthPrefix;
use \SplitIO\Test\Utils\SocketServerRemoteControl;


class SocketCloseRequested extends \Exception
{
}

class SocketServer
{
    private $interactions;
    private $socket;
    private $lp;
    private $connectionsToAccept;

    public function __construct($input)
    {
        $setup = $input["setup"];

        $this->lp = new LengthPrefix();
        $this->interactions = $input["interactions"];
        $this->connectionsToAccept = $setup["connectionsToAccept"] ?? 1;
        switch ($setup["socketType"]) {
            case SocketServerRemoteControl::UNIX_STREAM:
                $addressFamily = AF_UNIX;
                $socketType = SOCK_STREAM;
                break;
            case SocketServerRemoteControl::UNIX_SEQPACKET:
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
        posix_kill(posix_getppid(), SIGUSR1);
        while ($this->connectionsToAccept-- > 0) {
            $clientSock = null;
            try {
                if (($clientSock = @socket_accept($this->socket)) === false) {
                    throw new \Exception("socket_accept() failed: reason: "
                        . socket_strerror(socket_last_error($this->socket)) . "\n");
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
                $this->lp->ReadFrame($clientSock, $buf);
                if ($buf != base64_decode($testCase['expects'])) {
                    throw new \Exception("incoming value mismatch. Expected='"
                        . base64_decode($testCase['expects']) . "' / Actual='" . $buf . "'");
                }
            }

            if (isset($testCase['actions_during'])) {
                $this->handleAction($testCase['actions_during']);
            }

            if (isset($testCase['returns'])) {
                if (false === $this->lp->SendFrame($clientSock, base64_decode($testCase["returns"]))) {
                    throw new \Exception("failed to send 'return' value via socket: "
                        . socket_strerror(socket_last_error($clientSock)) . "\n");
                }
            }
        } finally {
            posix_kill(posix_getppid(), SIGUSR2);
        }
    }

    private function handleAction($action)
    {
        switch ($action['type'] ?? 'none') {
            case 'break':
                throw new \SocketCloseRequested();
            case 'delay':
                usleep($action['us']);
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
$contents = "";
while (!feof(STDIN)) {
    $contents .= fread(STDIN, 9999999);
}
fclose(STDIN);

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
