<?php

namespace SplitIO\ThinClient\Config;

use \Psr\Log\LoggerInterface;
use \Psr\Log\LogLevel;

class Logging
{
    private ?LoggerInterface $psrLogger;
    private string $level;

    private function __construct(LoggerInterface|null $psrLogger, string $level)
    {
        $this->psrLogger = $psrLogger;
        $this->level = $level;
    }

    public function logger(): LoggerInterface | null
    {
        return $this->psrLogger;
    }

    public function level(): string
    {
        return $this->level;
    }

    public static function fromArray(array $config): Logging
    {
        $d = self::default();
        return new Logging(
            $config['psr-instance'] ?? $d->logger(),
            $config['level'] ?? $d->level()
        );
    }

    public static function default(): Logging
    {
        return new Logging(null, LogLevel::INFO);
    }
}
