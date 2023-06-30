<?php

namespace SplitIO\ThinSdk\Link\Consumer;

use SplitIO\ThinSdk\Link\Protocol\Version;
use SplitIO\ThinSdk\Link\Transfer\ConnectionFactory;
use SplitIO\ThinSdk\Link\Serialization\SerializerFactory;
use SplitIO\ThinSdk\Config;

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

