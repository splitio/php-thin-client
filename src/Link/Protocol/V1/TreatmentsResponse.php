<?php

namespace SplitIO\ThinSdk\Link\Protocol\V1;

use SplitIO\ThinSdk\Link\Protocol\V1\Result;
use SplitIO\ThinSdk\Foundation\Lang\Enforce;

class TreatmentsResponse extends Response
{

    private /*array*/ $evaluationResults;

    public function __construct(Result $status, array $results)
    {
        parent::__construct($status);
        $this->evaluationResults = $results;
    }

    public function getEvaluationResults(): array
    {
        return $this->evaluationResults;
    }

    public function getEvaluationResult(int $index): ?EvaluationResult
    {
        return count($this->evaluationResults) > $index ? $this->evaluationResults[$index] : null;
    }

    public static function fromRaw(/*mixed*/$raw)/*: mixed*/
    {
        if (!is_array($raw)) {
            throw new \InvalidArgumentException("TreatmentResponse must be parsed from an array. Got a " . gettype($raw));
        }

        return new TreatmentsResponse(
            Result::from(Enforce::isInt($raw['s'])),
            array_map(
                function ($item) {
                    return EvaluationResult::fromRaw(Enforce::isArray($item));
                },
                Enforce::isArray($raw['p']['r'])
            )
        );
    }
}
