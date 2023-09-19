<?php

namespace SplitIO\ThinSdk\Link\Protocol\V1;

use  SplitIO\ThinSdk\Link\Protocol\V1\Result;
use SplitIO\ThinSdk\Foundation\Lang\Enforce;

class RegisterResponse extends Response
{
    public static function fromRaw(/*array*/$raw)/*: mixed*/
    {
        Enforce::isArray($raw);
        return new RegisterResponse(Result::from(Enforce::isInt($raw['s'])));
    }
}
