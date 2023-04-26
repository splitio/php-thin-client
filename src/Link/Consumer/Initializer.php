<?php

namespace SplitIO\ThinClient\Link\Consumer;

use SplitIO\ThinClient\Link\Protocol\Version;
use SplitIO\ThinClient\Config;

class Initializer
{
    static function setup(
        Version $version,
        Config\Transfer $transferConfig,
        Config\Serialization $serializationConfig,
        Config\Utils $utilityConfig): Manager
    {
        switch ($version) {
        case Version::V1:
            return new V1Manager($transferConfig, $serializationConfig, $utilityConfig);
        }
    }
}

