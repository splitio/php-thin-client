<?php

namespace SplitIO\ThinClient\Link\Protocol;


class RPC
{
	private Version $version;
	private OpCode $opcode;
	private array $args;

	private function __construct(Version $version, OpCode $opcode, array $args)
	{
		$this->version = $version;
		$this->opcode = $opcode;
		$this->args = $args;
	}

	public function getVersion(): Version
	{
		return $this->version;
	}

	public function getOpCode(): OpCode
	{
		return $this->opcode;
	}

	public function getArgs(): array
	{
		return $this->args;
	}

	public function toArray(): array
	{
		return array(
			"Version" => $this->getVersion()->value,
			"OpCode"  => $this->getOpCode()->value,
			"Args"    => $this->getArgs(),
		);
	}

	public static function forRegister(string $id): RPC
	{
		return new RPC(
			Version::V1,
			OpCode::Register,
			array(
				RegisterArgs::ID->value          => $id,
				RegisterArgs::SDK_VERSION->value => "php-lw-0.0.1",
			)
		);
	}

	public static function forTreatment(string $key, ?string $bucketingKey, string $feature, ?array $attributes): RPC
	{
		return new RPC(
			Version::V1,
			OpCode::Treatment,
			array(
				TreatmentArgs::KEY->value           => $key,
				TreatmentArgs::BUCKETING_KEY->value => $bucketingKey,
				TreatmentArgs::FEATURE->value       => $feature,
				TreatmentArgs::ATTRIBUTES->value    => $attributes,
			)
		);
	}
}
