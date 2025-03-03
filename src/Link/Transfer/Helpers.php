<?php

namespace SplitIO\ThinSdk\Link\Transfer;

class Helpers
{
    public static function getSocketError(/*\Socket|null*/$sock): string
    {
        $errc = socket_last_error($sock);
        if ($errc == 0) {
            return "no error returned";
        }
        return socket_strerror($errc);
    }

    public static function wrapSocketOperation($op, $socket, &$buffer, $size, $flags, $attempts = 3): int
    {

        // check operation is valid
        switch ($op) {
            case 'socket_send':
            case 'socket_recv':
                break;
            default:
                throw new \Exception("invalid socket operation: " . $op);
        }

        do {
            $res = @$op($socket, $buffer, $size, $flags);
            if ($res != false) {
                return $res;
            }

            $err = @socket_last_error($socket);
            switch ($err) {
                case SOCKET_EAGAIN:
                case SOCKET_EWOULDBLOCK:
                    $attempts--;
                case SOCKET_EINTR:
                    continue;
                default:
                    throw new ConnectionException("error writing to socket: " . @socket_strerror($err), $err);
            }
        } while ($attempts > 0);
  }
}
