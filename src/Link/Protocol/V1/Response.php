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

    static abstract function fromRaw(/*mixed*/ $raw)/*: mixed*/;
}
