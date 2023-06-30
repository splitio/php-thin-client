<?php

namespace SplitIO\ThinSdk\Link\Transfer;

class Helpers
{
    public static function getSocketError(/*\Socket|null*/ $sock): string
    {
        $errc = socket_last_error($sock);
        if ($errc == 0) {
            return "no error returned";
        }
        return socket_strerror($errc);
    }
}
