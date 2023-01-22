<?php

namespace SplitIO\ThinClient;

class Factory implements FactoryInterface
{
	private Config\Main $config;
	private Link\Manager $linkManager;

	private function __construct(Config\Main $config)
	{
		$this->config = $config;
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
		return new Client($this->linkManager);
	}
};
