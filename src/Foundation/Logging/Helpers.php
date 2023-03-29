<?php

namespace SplitIO\ThinClient\Foundation\Logging;

class Helpers
{
    public static function getLogger(\SplitIO\ThinClient\Config\Logging $config): \Psr\Log\LoggerInterface
    {
        return $config->logger() ?? BasicLogger::default($config->level());
    }
}
