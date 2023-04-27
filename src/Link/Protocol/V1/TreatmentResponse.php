<?php

namespace SplitIO\ThinClient\Link\Protocol\V1;

use SplitIO\ThinClient\Link\Protocol\V1\Result;

class TreatmentResponse extends Response
{

    private /*string*/ $treatment;
    private /*?ImpressionListenerData*/ $listenerData;

    public function __construct(Result $result, string $treatment, ?ImpressionListenerData $listenerData)
    {
        parent::__construct($result);
        $this->treatment = $treatment;
        $this->listenerData = $listenerData;
    }

    public function getTreatment(): string
    {
        return $this->treatment;
    }

    public function getListenerData(): ?ImpressionListenerData
    {
        return $this->listenerData;
    }

    static function fromRaw(/*mixed*/ $raw)/*: mixed*/
    {
        if (!is_array($raw)) {
            throw new \InvalidArgumentException("TreatmentResponse must be parsed from an array. Got a " . gettype($raw));
        }

        return new TreatmentResponse(
            Result::from($raw['s']),
            $raw['p']['t'],
            isset($raw['p']['l']) ? ImpressionListenerData::fromRaw($raw['p']['l']) : null
        );
    }
}
