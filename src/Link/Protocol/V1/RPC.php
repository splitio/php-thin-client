<?php

namespace SplitIO\ThinSdk\Link\Protocol\V1;

use  SplitIO\ThinSdk\Link\Protocol\Version;
use  SplitIO\ThinSdk\Link\Serialization\Serializable;

class RPC implements Serializable
{
    private /*Version*/ $version;
    private /*OpCode*/ $opcode;
    private /*array*/ $args;

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
        $v = \SplitIO\ThinSdk\Version::CURRENT;
        return new RPC(
            Version::V1(),
            OpCode::Register(),
            [
                RegisterArgs::ID()->getValue()          => $id,
                RegisterArgs::SDK_VERSION()->getValue() => "Splitd_PHP-$v",
                RegisterArgs::FLAGS()->getValue()       => $registerFlags->get(),
            ]
        );
    }

    public static function forTreatment(string $key, ?string $bucketingKey, string $feature, ?array $attributes): RPC
    {
        return new RPC(
            Version::V1(),
            OpCode::Treatment(),
            array(
                TreatmentArgs::KEY()->getValue()           => $key,
                TreatmentArgs::BUCKETING_KEY()->getValue() => $bucketingKey,
                TreatmentArgs::FEATURE()->getValue()       => $feature,
                TreatmentArgs::ATTRIBUTES()->getValue()    => ($attributes != null && count($attributes) > 0)
                    ? $attributes
                    : null,
            )
        );
    }

    public static function forTreatments(string $key, ?string $bucketingKey, array $features, ?array $attributes): RPC
    {
        return new RPC(
            Version::V1(),
            OpCode::Treatments(),
            array(
                TreatmentsArgs::KEY()->getValue()           => $key,
                TreatmentsArgs::BUCKETING_KEY()->getValue() => $bucketingKey,
                TreatmentsArgs::FEATURES()->getValue()      => $features,
                TreatmentsArgs::ATTRIBUTES()->getValue()    => ($attributes != null && count($attributes) > 0)
                    ? $attributes
                    : null,
            )
        );
    }

    public static function forTrack(
        string $key,
        string $trafficType,
        string $eventType,
        ?float $value,
        ?array $properties
    ): RPC {
        return new RPC(
            Version::V1(),
            OpCode::Track(),
            array(
                TrackArgs::KEY()->getValue()            => $key,
                TrackArgs::TRAFFIC_TYPE()->getValue()   => $trafficType,
                TrackArgs::EVENT_TYPE()->getValue()     => $eventType,
                TrackArgs::VALUE()->getValue()          => $value,
                TrackArgs::PROPERTIES()->getValue()     => $properties,
            )
        );
    }

    function getSerializable() /* : mixed */
    {
        return array(
            "v"  => $this->getVersion()->getValue(),
            "o"  => $this->getOpCode()->getValue(),
            "a"  => $this->getArgs(),
        );
    }
}
