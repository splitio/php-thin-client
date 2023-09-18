<?php

namespace SplitIO\ThinSdk\Link\Protocol\V1;

use  SplitIO\ThinSdk\Link\Protocol\V1\Result;
use  SplitIO\ThinSdk\Link\Serialization\Deserializable;

abstract class Response implements Deserializable
{

    private /*Result*/ $result;

    public function __construct(Result $result)
    {
        $this->result = $result;
    }

    public function getResult(): Result
    {
        return $this->result;
    }

    public function ensureSuccess(): void
    {
        if ($this->result != Result::Ok()) {
            throw new OperationFailureException("operation failed with status code: " . $this->result->getKey());
        }
    }
}
