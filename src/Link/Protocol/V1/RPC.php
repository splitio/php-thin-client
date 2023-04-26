<?php

namespace SplitIO\ThinClient\Link\Protocol\V1;

use  SplitIO\ThinClient\Link\Protocol\Version;
use  SplitIO\ThinClient\Link\Serialization\Serializable;

class RPC implements Serializable
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

    public static function forRegister(string $id, RegisterFlags $registerFlags): RPC
    {
        $v = \SplitIO\ThinClient\Version::CURRENT;
        return new RPC(
            Version::V1,
            OpCode::Register,
            [
                RegisterArgs::ID->value          => $id,
                RegisterArgs::SDK_VERSION->value => "Splitd_PHP-$v",
                RegisterArgs::FLAGS->value       => $registerFlags->get(),
            ]
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

    function getSerializable(): int|float|array|string|null
    {
        return array(
            "v"  => $this->getVersion()->value,
            "o"  => $this->getOpCode()->value,
            "a"  => $this->getArgs(),
        );
    }
}
