<?php

namespace SplitIO\ThinClient\Link\Transfer;

class UnixStream implements RawConnection
{
    private /*string*/ $targetSockFN;
    private /*\Socket*/ $sock;
    private /*Framing\Framer*/ $framer;

    public function __construct(string $targetSockFN, array $options = array())
    {
        $this->targetSockFN = $targetSockFN;
        $this->framer = new Framing\LengthPrefix();

        if (!$this->sock = @socket_create(AF_UNIX, SOCK_STREAM, 0)) {
            throw new ConnectionException(
                "failed to create a socket: "
                    . Helpers::getSocketError(null)
            );
        }

        if (isset($options['timeout'])) {
            @socket_set_option($this->sock, SOL_SOCKET, SO_RCVTIMEO, $options['timeout']);
            @socket_set_option($this->sock, SOL_SOCKET, SO_SNDTIMEO, $options['timeout']);
        }

        if (!@socket_connect($this->sock, $this->targetSockFN)) {
            throw new ConnectionException(
                "failed to connect to remote socket $this->targetSockFN:"
                    . Helpers::getSocketError($this->sock)
            );
        }
    }

    public function sendMessage(string $message)
    {
        $this->framer->SendFrame($this->sock, $message);
    }

    public function readMessage(): string
    {
        $buffer = "";
        $this->framer->ReadFrame($this->sock, $buffer);
        return $buffer;
    }

    public function isHealthy(): bool
    {
        $status = @socket_get_status($this->sock);
        return (!$status['timed_out'] && !$status['eof']);
    }
}
