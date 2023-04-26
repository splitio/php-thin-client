<?php

namespace SplitIO\ThinClient\Link\Protocol\V1;

use  SplitIO\ThinClient\Link\Serialization\Deserializable;

class ImpressionListenerData implements Deserializable
{
    private string $label;
    private int $timestamp;

    public function __construct(string $label, string $timestamp)
    {
        $this->label = $label;
        $this->timestamp = $timestamp;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getTimestamp(): int
    {
        return $this->timestamp;
    }

    public static function fromRaw(mixed $raw): mixed
    {
        if (!is_array($raw)) {
            throw new \InvalidArgumentException("TreatmentResponse must be parsed from an array. Got a " . gettype($raw));
        }

        return new ImpressionListenerData($raw['l'], $raw['m']);
    }
}
