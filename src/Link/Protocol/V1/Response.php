<?php

namespace SplitIO\ThinClient\Link\Protocol\V1;

use  SplitIO\ThinClient\Link\Protocol\V1\Result;
use  SplitIO\ThinClient\Link\Serialization\Deserializable;

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
