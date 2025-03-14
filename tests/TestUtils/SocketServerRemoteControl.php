<?php

namespace SplitIO\Test\TestUtils;

function debug($str)
{
    if (getenv("DEBUG") == "true") {
        fwrite(STDERR, $str . "\n");
    }
}

class SocketServerRemoteControl
{

    const UNIX_STREAM = 1;
    const UNIX_SEQPACKET = 2;

    private $server_script_path = __DIR__ . "/_server.php";
    private $pipes = [];
    private $subprocessHandle;
    private $subprocessPid;
    private $started = false;
    private $ready = false;
    private $done = 0;
    private $finished = false;

    public function __construct()
    {
        $descs = [
            0 => ['pipe', 'r'],
        ];

        pcntl_async_signals(true);
        pcntl_signal(SIGUSR1, [$this, 'sigHandler'], true);
        pcntl_signal(SIGUSR2, [$this, 'sigHandler'], true);
        pcntl_signal(SIGCHLD, [$this, 'sigHandler'], true);

        $this->subprocessHandle = proc_open('php ' . $this->server_script_path, $descs, $this->pipes);
        if (!is_resource($this->subprocessHandle)) {
            throw new \Exception("failed to create process");
        }

        debug("process created");

        $this->subprocessPid = proc_get_status($this->subprocessHandle)['pid'];
    }

    public function start(string $socketType, string $socketAddress, int $connectionsToAccept, array $interactions): void
    {
        if ($this->started) {
            throw new \Exception("socket server is already running.");
        }

        if (in_array($socketType, [self::UNIX_STREAM, self::UNIX_SEQPACKET]) && file_exists($socketAddress)) {
            unlink(realpath($socketAddress));
        }

        $data = json_encode([
            "setup" => [
                "parentPid" => posix_getpid(),
                "socketType" => $socketType,
                "socketAddress" => $socketAddress,
                "connectionsToAccept" => $connectionsToAccept,
            ],
            "interactions" => array_map([self::class, 'encodeInteraction'], $interactions),
        ]);

        debug("writing stdin");

        $sum = 0;
        foreach (str_split($data, 4 * 1024) as $chunk) {
            $sum += fwrite($this->pipes[0], $chunk, strlen($chunk));
        }

        fclose($this->pipes[0]);
        $this->started = true;

        debug("started");
    }

    public function awaitServerReady(): void
    {
        while (!$this->ready) usleep(1000); // sleep 1 millisecond
        $this->ready = false;
    }

    public function awaitDone(int $done): void
    {
        while ($this->done < $done) usleep(1000); // sleep 100 millisecond
    }

    public function awaitFinished(): void
    {
        while (!$this->finished) usleep(1000);
    }

    public function shutdown(): void
    {
        debug("shutting down");
        if (!$this->started) {
            fclose($this->pipes[0]);
        }

        // proc_terminate is async, we need to wait (but not too long), and kill the server
        // if it doesn't gracefully quit
        try {
            proc_terminate($this->subprocessHandle);
            $status = proc_get_status($this->subprocessHandle);
            if (!$status['running']) {
                return;
            }

            sleep(1);
            $status = proc_get_status($this->subprocessHandle);
            if (!$status['running']) {
                return;
            }

            debug("process didn't shutdown gracefully. sending SIGKILL");
            proc_terminate($this->subprocessHandle, SIGKILL);
            sleep(1);
            $status = proc_get_status($this->subprocessHandle);
            if (!$status['running']) {
                return;
            }

            debug("process didn't finish 1 second after being killed");
        } finally {
            debug("process closed");
        }
    }

    private static function encodeInteraction(array $interaction): array
    {
        $result = [];
        foreach ($interaction as $k => $v) {
            if (in_array($k, ['expects', 'returns'])) {
                $result[$k] = base64_encode($v);
            } else {
                $result[$k] = $v;
            }
        }
        return $result;
    }

    // This method is public only so it can be used as a signal handling callback
    public function sigHandler($signo, $siginfo)
    {
        debug("SIGNAL RECEIVED IN PARENT PROCESS: " . var_export($siginfo, true));
        switch ($signo) {
            case SIGUSR1:
                $this->ready = true;
                break;
            case SIGUSR2:
                $this->done++;
                break;
            case SIGCHLD:
                pcntl_waitpid($this->subprocessPid, $status);
                if (pcntl_wifexited($status) && pcntl_wexitstatus($status) != 0) {
                    throw new \Exception("socket server ended in error");
                }
                $this->finished = true;
                break;
            default:
                throw new \Exception("Unexpected signal $signo: " . var_export($siginfo, true));
        }
    }
}
