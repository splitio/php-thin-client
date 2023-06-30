<?php

namespace SplitIO\ThinSdk\Foundation\Logging;

class Helpers
{
    public static function getLogger(\SplitIO\ThinSdk\Config\Logging $config): \Psr\Log\LoggerInterface
    {
        return $config->logger() ?? BasicLogger::default($config->level());
    }
}
