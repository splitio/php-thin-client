<?php

namespace SplitIO\ThinClient\Link\Transfer;

class ConnectionFactory
{

    private /*array*/ $sockOpts;
    private /*string*/ $sockAddr;
    private /*string*/ $sockType;

    public function __construct(\SplitIO\ThinClient\Config\Transfer $options)
    {
        $this->sockType = $options->connType();
        $this->sockAddr = $options->sockFN();
        $this->sockOpts = array_filter([
            'timeout' => self::formatTimeout($options->timeout()),
            'sendBufferSize' => $options->bufferSize(),
            'recvBufferSize' => $options->bufferSize(),
        ]);

    }

    public function create(): RawConnection
    {
        switch ($this->sockType) {
            case 'unix-seqpacket':
                return new UnixPacket($this->sockAddr, $this->sockOpts);
            case 'unix-stream':
                return new UnixStream($this->sockAddr, $this->sockOpts);
        }
        throw new \Exception("invalid connection type " . $this->sockType);
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
