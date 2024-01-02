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
        return self::_forTreatment($key, $bucketingKey, $feature, $attributes, false);
    }

    public static function forTreatmentWithConfig(string $key, ?string $bucketingKey, string $feature, ?array $attributes): RPC
    {
        return self::_forTreatment($key, $bucketingKey, $feature, $attributes, true);
    }

    public static function forTreatments(string $key, ?string $bucketingKey, array $features, ?array $attributes): RPC
    {
        return self::_forTreatments($key, $bucketingKey, $features, $attributes, false);
    }

    public static function forTreatmentsWithConfig(string $key, ?string $bucketingKey, array $features, ?array $attributes): RPC
    {
        return self::_forTreatments($key, $bucketingKey, $features, $attributes, true);
    }

    public static function forTreatmentsByFlagSet(
        string $key,
        ?string $bucketingKey,
        string $flagSet,
        ?array $attributes): RPC
    {
        return self::_forTreatmentsByFlagSet($key, $bucketingKey, $flagSet, $attributes, false);
    }

    public static function forTreatmentsWithConfigByFlagSet(
        string $key,
        ?string $bucketingKey,
        string $flagSet,
        ?array $attributes): RPC
    {
        return self::_forTreatmentsByFlagSet($key, $bucketingKey, $flagSet, $attributes, true);
    }

    public static function forTreatmentsByFlagSets(
        string $key,
        ?string $bucketingKey,
        array $flagSets,
        ?array $attributes): RPC
    {
        return self::_forTreatmentsByFlagSets($key, $bucketingKey, $flagSets, $attributes, false);
    }

    public static function forTreatmentsWithConfigByFlagSets(
        string $key,
        ?string $bucketingKey,
        array $flagSets,
        ?array $attributes): RPC
    {
        return self::_forTreatmentsByFlagSets($key, $bucketingKey, $flagSets, $attributes, true);
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

    public static function forSplitNames(): RPC
    {
        return new RPC(Version::V1(), OpCode::SplitNames(), []);
    }

    public static function forSplit(string $splitName): RPC
    {
        return new RPC(Version::V1(), OpCode::Split(), [SplitArgs::SPLIT_NAME()->getValue() => $splitName]);
    }

    public static function forSplits(): RPC
    {
        return new RPC(Version::V1(), OpCode::Splits(), []);
    }

    function getSerializable() /* : mixed */
    {
        return array(
            "v"  => $this->getVersion()->getValue(),
            "o"  => $this->getOpCode()->getValue(),
            "a"  => $this->getArgs(),
        );
    }

    private static function _forTreatment(string $k, ?string $bk, string $f, ?array $a, bool $includeConfig): RPC
    {
        return new RPC(
            Version::V1(),
            $includeConfig ? OpCode::TreatmentWithConfig() : OpCode::Treatment(),
            array(
                TreatmentArgs::KEY()->getValue()           => $k,
                TreatmentArgs::BUCKETING_KEY()->getValue() => $bk,
                TreatmentArgs::FEATURE()->getValue()       => $f,
                TreatmentArgs::ATTRIBUTES()->getValue()    => ($a != null && count($a) > 0) ? $a : null,
            )
        );
    }

    public static function _forTreatments(string $k, ?string $bk, array $f, ?array $a, bool $includeConfig): RPC
    {
        return new RPC(
            Version::V1(),
            $includeConfig ? OpCode::TreatmentsWithConfig() : OpCode::Treatments(),
            [
                TreatmentsArgs::KEY()->getValue()           => $k,
                TreatmentsArgs::BUCKETING_KEY()->getValue() => $bk,
                TreatmentsArgs::FEATURES()->getValue()      => $f,
                TreatmentsArgs::ATTRIBUTES()->getValue()    => ($a != null && count($a) > 0) ? $a : null,
            ]
        );
    }

    public static function _forTreatmentsByFlagSet(
        string $k,
        ?string $bk,
        string $f,
        ?array $a,
        bool $includeConfig): RPC
    {
        return new RPC(
            Version::V1(),
            $includeConfig ? OpCode::TreatmentsWithConfigByFlagSet() : OpCode::TreatmentsByFlagSet(),
            [
                TreatmentsByFlagSetArgs::KEY()->getValue()           => $k,
                TreatmentsByFlagSetArgs::BUCKETING_KEY()->getValue() => $bk,
                TreatmentsByFlagSetArgs::FLAG_SET()->getValue()      => $f,
                TreatmentsByFlagSetArgs::ATTRIBUTES()->getValue()    => ($a != null && count($a) > 0) ? $a : null,
            ]
        );
    }

    public static function _forTreatmentsByFlagSets(
        string $k,
        ?string $bk,
        array $f,
        ?array $a,
        bool $includeConfig): RPC
    {
        return new RPC(
            Version::V1(),
            $includeConfig ? OpCode::TreatmentsWithConfigByFlagSets() : OpCode::TreatmentsByFlagSets(),
            [
                TreatmentsByFlagSetsArgs::KEY()->getValue()           => $k,
                TreatmentsByFlagSetsArgs::BUCKETING_KEY()->getValue() => $bk,
                TreatmentsByFlagSetsArgs::FLAG_SETS()->getValue()      => $f,
                TreatmentsByFlagSetsArgs::ATTRIBUTES()->getValue()    => ($a != null && count($a) > 0) ? $a : null,
            ]
        );
    }
}
