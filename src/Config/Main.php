<?php

namespace SplitIO\ThinClient\Config;

class Main
{
    private Transfer $transfer;
    private Serialization $serialization;
    private Logging $logging;

    private function __construct(Transfer $transfer, Serialization $serialization, Logging $logging)
    {
        $this->transfer = $transfer;
        $this->serialization = $serialization;
        $this->logging = $logging;
    }

    public function transfer(): Transfer
    {
        return $this->transfer;
    }

    public function serialization(): Serialization
    {
        return $this->serialization;
    }

    public function logging(): Logging
    {
        return $this->logging;
    }

    public static function fromArray(array $config): Main
    {
        return new Main(
            Transfer::fromArray($config['transfer']           ?? []),
            Serialization::fromArray($config['serialization'] ?? []),
            Logging::fromArray($config['logging'] ?? []),
        );
    }

    public static function default(): Main
    {
        return new Main(
            Transfer::default(),
            Serialization::default(),
            Logging::default(),
        );
    }
}
