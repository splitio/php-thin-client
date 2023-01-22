<?php

namespace SplitIO\ThinClient\Config;

class Serialization
{
	private string $mechanism;

	private function __construct(string $mechanism)
	{
		$this->mechanism = $mechanism;
	}

	public function mechanism(): string
	{
		return $this->mechanism;
	}

	public static function fromArray(array $config): Serialization
	{
		$d = self::default();
		return new Serialization($config['mechanism'] ?? $d->mechanism());
	}

	public static function default(): Serialization
	{
		return new Serialization(
			'msgpack'
		);
	}
}
