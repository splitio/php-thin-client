<?php

namespace SplitIO\ThinSdk\Link\Protocol\V1;

use SplitIO\ThinSdk\Link\Serialization\Deserializable;
use SplitIO\ThinSdk\Foundation\Lang\Enforce;

class ImpressionListenerData implements Deserializable
{
    private /*string*/ $label;
    private /*int*/ $timestamp;
    private /*int*/ $changeNumber;

    public function __construct(string $label, int $changeNumber, int $timestamp)
    {
        $this->label = $label;
        $this->changeNumber = $changeNumber;
        $this->timestamp = $timestamp;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getChangeNumber(): int
    {
        return $this->changeNumber;
    }

    public function getTimestamp(): int
    {
        return $this->timestamp;
    }

    public static function fromRaw(/*mixed*/$raw)/*: mixed*/
    {
        if (!is_array($raw)) {
            throw new \InvalidArgumentException("TreatmentResponse must be parsed from an array. Got a " . gettype($raw));
        }

        return new ImpressionListenerData(Enforce::isString($raw['l']), Enforce::isInt($raw['c']), enforce::isInt($raw['m']));
    }
}
