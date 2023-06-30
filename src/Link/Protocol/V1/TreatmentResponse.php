<?php

namespace SplitIO\ThinSdk\Link\Protocol\V1;

use SplitIO\ThinSdk\Link\Protocol\V1\Result;
use SplitIO\ThinSdk\Foundation\Lang\Enforce;

class TreatmentResponse extends Response
{

    private $evaluationResult;

    public function __construct(Result $status, EvaluationResult $result)
    {
        parent::__construct($status);
        $this->evaluationResult = $result;
    }

    public function getEvaluationResult(): EvaluationResult
    {
        return $this->evaluationResult;
    }

    static function fromRaw(/*mixed*/ $raw)/*: mixed*/
    {
        if (!is_array($raw)) {
            throw new \InvalidArgumentException("TreatmentResponse must be parsed from an array. Got a " . gettype($raw));
        }

        return new TreatmentResponse(
            Result::from(Enforce::isInt($raw['s'])),
            EvaluationResult::fromRaw(Enforce::isArray($raw['p'])));
    }
}
