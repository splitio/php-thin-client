<?php

namespace SplitIO\ThinClient\Link\Transfer;

class UnixStream implements RawConnection
{
    private string $targetSockFN;
    private \Socket $sock;
    private Framing\Framer $framer;

    public function __construct(string $targetSockFN)
    {
        $this->targetSockFN = $targetSockFN;
        $this->framer = new Framing\LengthPrefix();

        if (!$this->sock = socket_create(AF_UNIX, SOCK_STREAM, 0)) {
            throw new ConnectionException(
                "failed to create a socket: "
                    . Helpers::getSocketError(null)
            );
        }

        if (!socket_connect($this->sock, $this->targetSockFN)) {
            throw new ConnectionException(
                "failed to connect to remote socket $this->targetSockFN:"
                    . Helpers::getSocketError($this->sock)
            );
        }
    }

    public function sendMessage(string $message)
    {
        $toSend = $this->framer->Frame($message);
        if (socket_send($this->sock, $toSend, strlen($toSend), 0) != strlen($toSend)) {
            throw new ConnectionException(
                "error writing to socket: "
                    . Helpers::getSocketError($this->sock)
            );
        }
    }

    public function readMessage(): string
    {
        $buffer = "";
        $n = $this->framer->ReadFrame($this->sock, $buffer);
        if (!$n) {
            throw new ConnectionException(
                "error reading from socket: "
                    . Helpers::getSocketError($this->sock)
            );
        }
        return substr($buffer, 0, $n);
    }

    public function isHealthy(): bool
    {
        $status = socket_get_status($this->sock);
        return (!$status['timed_out'] && !$status['eof']);
    }
}
