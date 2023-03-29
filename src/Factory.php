<?php

namespace SplitIO\ThinClient;

use SplitIO\ThinClient\Foundation\Logging\Helpers;

class Factory implements FactoryInterface
{
    private Config\Main $config;
    private Link\Manager $linkManager;
    private \Psr\Log\LoggerInterface $logger;

    private function __construct(Config\Main $config)
    {
        $this->config = $config;
        $this->logger = Helpers::getLogger($config->logging());
        $this->linkManager = new Link\Manager($config->transfer(), $config->serialization());
    }

    public static function default(): Factory
    {
        return new Factory(Config\Main::default());
    }

    public static function withConfig(array $config): Factory
    {
        return new Factory(Config\Main::fromArray($config));
    }

    public function client(): Client
    {
        return new Client($this->linkManager, $this->logger);
    }
};
