<?php

namespace SplitIO\ThinSdk\Link\Protocol\V1;

use SplitIO\ThinSdk\Foundation\Lang\Enforce;

class SplitViewResult
{
    private /*string*/ $name;
    private /*string*/ $trafficType;
    private /*bool*/ $killed;
    private /*array*/ $treatments;
    private /*int*/ $changeNumber;
    private /*string*/ $defaultTreatment;
    private /*array*/ $sets;
    private /*array*/ $configs;

    public function __construct(
        string $name,
        string $trafficType,
        bool $killed,
        array $treatments,
        int $changeNumber,
        string $defaultTreatment,
        array $sets,
        ?array $configs
        )
    {
        $this->name = $name;
        $this->trafficType = $trafficType;
        $this->killed = $killed;
        $this->treatments = $treatments;
        $this->changeNumber = $changeNumber;
        $this->defaultTreatment = $defaultTreatment;
        $this->sets = $sets;
        $this->configs = $configs;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getTrafficType(): string
    {
        return $this->trafficType;
    }

    public function getKilled(): bool
    {
        return $this->killed;
    }

    public function getTreatments(): array
    {
        return $this->treatments;
    }

    public function getChangeNumber(): int
    {
        return $this->changeNumber;
    }

    public function getConfigs(): ?array
    {
        return $this->configs;
    }

    public function getDefaultTreatment(): string
    {
        return $this->defaultTreatment;
    }

    public function getSets(): array
    {
        return $this->sets;
    }

    public static function fromRaw(array $raw): SplitViewResult
    {
        return new SplitViewResult(
            Enforce::isString($raw['n']),
            Enforce::isString($raw['t']),
            Enforce::isBool($raw['k']),
            Enforce::isArray($raw['s']),
            Enforce::isInt($raw['c']),
            Enforce::isString($raw['d']),
            isset($raw['e']) ? Enforce::isArray($raw['e']) : [],
            isset($raw['f']) ? Enforce::isArray($raw['f']) : null
        );
    }
}
