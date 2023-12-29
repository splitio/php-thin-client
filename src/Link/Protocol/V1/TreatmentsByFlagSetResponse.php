<?php

namespace SplitIO\ThinSdk\Link\Protocol\V1;

use SplitIO\ThinSdk\Link\Protocol\V1\Result;
use SplitIO\ThinSdk\Foundation\Lang\Enforce;

class TreatmentsByFlagSetResponse extends Response
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
        Enforce::isArray($raw);
        $status = Result::from(Enforce::isInt($raw['s']));

        $results = [];
        foreach (Enforce::isArray($raw['p']['r']) as $feature => $evalResult) {
            $results[$feature] = EvaluationResult::fromRaw(Enforce::isArray($evalResult));
        };

        return new TreatmentsByFlagSetResponse(
            $status,
            Enforce::isArray($results)
        );
    }
}
