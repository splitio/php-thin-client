<?php

namespace SplitIO\ThinClient\Config;

use \SplitIO\ThinClient\Utils\ImpressionListener;


class Utils
{
    private /*?ImpressionListener*/ $listener;

    private function __construct(?ImpressionListener $listener)
    {
        $this->listener = $listener;
    }

    public function impressionListener(): ?ImpressionListener
    {
        return $this->listener;
    }

    public static function fromArray(array $config): Utils
    {
        $d = self::default();
        return new Utils($config['impressionListener'] ?? $d->impressionListener());
    }

    public static function default(): Utils
    {
        return new Utils(null);
    }
}
