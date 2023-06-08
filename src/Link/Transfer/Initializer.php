<?php

namespace SplitIO\ThinClient\Link\Transfer;

class Initializer
{
    public static function setup(\SplitIO\ThinClient\Config\Transfer $options): RawConnection
    {

        $sockOpts = array_filter([
            'timeout' => self::formatTimeout($options->timeout()),
            'sendBufferSize' => $options->bufferSize(),
            'recvBufferSize' => $options->bufferSize(),
        ]);

        switch ($options->connType()) {
            case 'unix-seqpacket':
                return new UnixPacket($options->sockFN(), $sockOpts);
            case 'unix-stream':
                return new UnixStream($options->sockFN(), $sockOpts);
        }

        throw new \Exception("invalid connection type " . $options->connType());
    }

    private static function formatTimeout(?int $milliseconds)/*: ?int */
    {
        if ($milliseconds == null) {
            $milliseconds = 1000;
        }

        return [
            'sec' => $milliseconds / 1000,
            'usec' => 0, // TODO(mredolatti): handle seconds fractions in usec units
        ];
    }
}
