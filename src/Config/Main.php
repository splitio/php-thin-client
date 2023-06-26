<?php

namespace SplitIO\ThinClient\Config;

class Main
{
    private /*Transfer*/ $transfer;
    private /*Serialization*/ $serialization;
    private /*Logging*/ $logging;
    private /*Utils*/ $utils;

    private function __construct(Transfer $transfer, Serialization $serialization, Logging $logging, Utils $utils)
    {
        $this->transfer = $transfer;
        $this->serialization = $serialization;
        $this->logging = $logging;
        $this->utils = $utils;
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

    public function utils(): Utils
    {
        return $this->utils;
    }

    public static function fromArray(array $config): Main
    {
        return new Main(
            Transfer::fromArray($config['transfer']           ?? []),
            Serialization::fromArray($config['serialization'] ?? []),
            Logging::fromArray($config['logging'] ?? []),
            Utils::fromArray($config['utils'] ?? []),
        );
    }

    public static function default(): Main
    {
        return new Main(
            Transfer::default(),
            Serialization::default(),
            Logging::default(),
            Utils::default(),
        );
    }
}
