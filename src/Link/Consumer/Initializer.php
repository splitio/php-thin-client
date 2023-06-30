<?php

namespace SplitIO\ThinClient\Link\Consumer;

use SplitIO\ThinClient\Link\Protocol\Version;
use SplitIO\ThinClient\Link\Transfer\ConnectionFactory;
use SplitIO\ThinClient\Link\Serialization\SerializerFactory;
use SplitIO\ThinClient\Config;

use Psr\Log\LoggerInterface;

class Initializer
{
    static function setup(
        Version $version,
        Config\Transfer $transferConfig,
        Config\Serialization $serializationConfig,
        Config\Utils $utilityConfig,
        LoggerInterface $logger): Manager
    {

        $connFactoy = new ConnectionFactory($transferConfig);
        $serializerFactory = new SerializerFactory($serializationConfig);

        switch ($version) {
        case Version::V1():
            return new V1Manager($connFactoy, $serializerFactory, $utilityConfig, $logger);
        }
    }
}

