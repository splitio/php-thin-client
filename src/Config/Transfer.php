<?php

namespace SplitIO\ThinClient\Config;

class Transfer
{
    private /*string*/ $sockFN;
    private /*string*/ $connType;
    private /*int*/ $timeout;
    private /*int*/ $bufferSize;

    private function __construct(string $sockFN, string $connType, ?int $timeout, ?int $bufferSize)
    {
        $this->sockFN = $sockFN;
        $this->connType = $connType;
        $this->timeout = $timeout;
        $this->bufferSize = $bufferSize;
    }

    public function sockFN(): string
    {
        return $this->sockFN;
    }

    public function connType(): string
    {
        return $this->connType;
    }

    public function timeout()/*: ?int */
    {
        return $this->timeout;
    }

    public function bufferSize()/*: ?int */
    {
        return $this->bufferSize;
    }

    public static function fromArray(array $config)
    {
        $d = self::default();
        return new Transfer(
            $config['address']    ?? $d->sockFN(),
            $config['type']       ?? $d->connType(),
            $config['timeout']    ?? $d->timeout(),
            $config['bufferSize'] ?? $d->bufferSize(),
        );
    }

    public static function default(): Transfer
    {
        return new Transfer(
            '/var/run/splitd.sock',
            'unix-seqpacket',
            null,
            null,
        );
    }
}
