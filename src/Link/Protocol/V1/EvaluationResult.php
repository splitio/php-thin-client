<?php

namespace SplitIO\ThinClient\Link\Protocol\V1;

use SplitIO\ThinClient\Foundation\Lang\Enforce;

class EvaluationResult
{

    private /*string*/ $treatment;
    private /*string*/ $ilData;
    private /*string*/ $config;

    public function __construct(string $treatment, ?ImpressionListenerData $ilData, ?string $config)
    {
        $this->treatment = $treatment;
        $this->ilData = $ilData;
        $this->config = $config;
    }

    public function getTreatment(): string
    {
        return $this->treatment;
    }

    public function getImpressionListenerData(): ?ImpressionListenerData
    {
        return $this->ilData;
    }

    public function getConfig(): ?string
    {
        return $this->config;
    }

    public static function fromRaw(array $raw): EvaluationResult
    { 
        return new EvaluationResult(Enforce::isString($raw['t']),
            isset($raw['l']) ? ImpressionListenerData::fromRaw(Enforce::isArray($raw['l'])) : null,
            isset($raw['c']) ? Enforce::isString($raw['c']) : null);
    }
}
