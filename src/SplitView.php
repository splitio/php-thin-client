<?php

namespace SplitIO\ThinSdk;

class SplitView
{
    private /*string*/ $name;
    private /*string*/ $trafficType;
    private /*bool*/ $killed;
    private /*array*/ $treatments;
    private /*int*/ $changeNumber;
    private /*array*/ $configs;

    public function __construct(string $name, string $trafficType, bool $killed, array $treatments, int $changeNumber, ?array $configs)
    {
        $this->name = $name;
        $this->trafficType = $trafficType;
        $this->killed = $killed;
        $this->treatments = $treatments;
        $this->changeNumber = $changeNumber;
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
}
