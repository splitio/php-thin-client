<?php

namespace SplitIO\ThinClient\Link\Transfer;

class Initializer
{
    public static function setup(\SplitIO\ThinClient\Config\Transfer $options): RawConnection
    {
        switch ($options->connType()) {
            case 'unix-seqpacket':
                return new UnixPacket($options->sockFN());
            case 'unix-stream':
                return new UnixStream($options->sockFN());
        }
        throw new \Exception("invalid connection type " . $options->connType());
    }
}
