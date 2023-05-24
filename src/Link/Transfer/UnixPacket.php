<?php

namespace SplitIO\ThinClient\Link\Transfer;

class UnixPacket implements RawConnection
{
    private /*string*/ $targetSockFN;
    private /*\Socket*/ $sock;
    private /*int*/ $maxRecvSize = 64 * 1024;

    public function __construct(string $targetSockFN, array $options = array())
    {
        $this->targetSockFN = $targetSockFN;

        // max size: /proc/sys/net/core/wmem_max - 32
        // https://www.ibm.com/docs/de/smpi/10.2?topic=mpi-tuning-your-linux-system
        // https://community.rti.com/static/documentation/perftest/3.0/tuning_os.html
        if (!$this->sock = socket_create(AF_UNIX, SOCK_SEQPACKET, 0)) {
            throw new ConnectionException("failed to create a socket: "
                . Helpers::getSocketError(null));
        }

        if (isset($options['timeout'])) {
            @socket_set_option($this->sock, SOL_SOCKET, SO_RCVTIMEO, $options['timeout']);
            @socket_set_option($this->sock, SOL_SOCKET, SO_SNDTIMEO, $options['timeout']);
        }

        if (isset($options['sendBufferSize'])) {
            if (@socket_set_option($this->sock, SOL_SOCKET, SO_SNDBUF, $options['sendBufferSize']) == false) {
                throw new ConnectionException("cannot allocate requested send-buffer size. please check your OS config");
            }
        }

        if (isset($options['recvBufferSize'])) {
            if (@socket_set_option($this->sock, SOL_SOCKET, SO_RCVBUF, $options['recvBufferSize']) == false) {
                throw new ConnectionException("cannot allocate requested receive-buffer size. please check your OS config");
            }
            $this->maxRecvSize = $options['recvBufferSize'];
        }

        if (!socket_connect($this->sock, $this->targetSockFN)) {
            throw new ConnectionException("failed to connect to remote socket $this->targetSockFN"
                . Helpers::getSocketError($this->sock));
        }
    }

    public function sendMessage(string $message)
    {
        if (@socket_send($this->sock, $message, strlen($message), 0) != strlen($message)) {
            throw new ConnectionException("error writing to socket: "
                . Helpers::getSocketError($this->sock));
        }
    }

    public function readMessage(): string
    {
        $buffer = "";
        $n = @socket_recv($this->sock, $buffer, $this->maxRecvSize, 0);
        if ($n == false) {
            echo get_debug_type($n) . "\n";
            throw new ConnectionException("error reading from socket: "
                . Helpers::getSocketError($this->sock));
        }
        return substr($buffer, 0, $n);
    }

    public function isHealthy(): bool
    {
        $status = @socket_get_status($this->sock);
        return (!$status['timed_out'] && !$status['eof']);
    }
}
