<?php

namespace SplitIO\ThinClient\Config;

class Transfer
{
    private string $sockFN;
    private string $connType;

    private function __construct(string $sockFN, string $connType)
    {
        $this->sockFN = $sockFN;
        $this->connType = $connType;
    }

    public function sockFN(): string
    {
        return $this->sockFN;
    }

    public function connType(): string
    {
        return $this->connType;
    }

    public static function fromArray(array $config)
    {
        $d = self::default();
        return new Transfer(
            $config['address'] ?? $d->sockFN(),
            $config['type']    ?? $d->connType(),
        );
    }

    public static function default(): Transfer
    {
        return new Transfer(
            '/var/run/splitd.sock',
            'unix-seqpacket'
        );
    }
}
