<?php

namespace SplitIO\ThinClient\Link\Transfer;

class StreamSocketHelpers
{

    public static function writeOrThrow(/*Socket*/ $socket, string $message): int
    {
        $sent = @socket_send($socket, $message, strlen($message), 0);
        if ($sent == false) {
            throw new ConnectionException("error writing to socket: " . Helpers::getSocketError($socket));
        }
        return $sent;
    }

    public static function readOrThrow(/*Socket*/ $socket, string &$buffer, int $length): int
    {
        $res = @socket_recv($socket, $buffer, $length, 0);
        if ($res == false) {
            throw new ConnectionException("error reading from socket: " . Helpers::getSocketError($socket));
        }
        return $res;
    }
}
