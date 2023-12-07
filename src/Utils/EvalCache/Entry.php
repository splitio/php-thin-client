<?php

namespace SplitIO\ThinSdk\Utils\EvalCache;

class Entry
{
    private /*string*/ $treatment;
    private /*string?*/ $config;
    private /*bool*/ $hascfg;

    public function __construct(string $treatment, bool $hasConfig = false, ?string $config = null)
    {
        $this->treatment = $treatment;
        $this->config = $config;
        $this->hascfg = $hasConfig;
    }

    public function getTreatment(): string
    {
        return $this->treatment;
    }

    public function getConfig(): ?string
    {
        return $this->config;
    }

    public function hasConfig(): bool
    {
        return $this->hascfg;
    }
}
