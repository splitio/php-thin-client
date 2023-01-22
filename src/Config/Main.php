<?php

namespace SplitIO\ThinClient\Config;

class Main
{
	private Transfer $transfer;
	private Serialization $serialization;

	private function __construct(Transfer $transfer, Serialization $serialization)
	{
		$this->transfer = $transfer;
		$this->serialization = $serialization;
	}

	public function transfer(): Transfer
	{
		return $this->transfer;
	}

	public function serialization(): Serialization
	{
		return $this->serialization;
	}

	public static function fromArray(array $config): Main
	{
		return new Main(
			Transfer::fromArray($config['transfer']           ?? []),
			Serialization::fromArray($config['serialization'] ?? [])
		);
	}

	public static function default(): Main
	{
		return new Main(
			Transfer::default(),
			Serialization::default()
		);
	}
}
