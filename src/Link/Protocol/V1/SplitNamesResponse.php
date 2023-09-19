<?php

namespace SplitIO\ThinSdk\Link\Protocol\V1;

use SplitIO\ThinSdk\Link\Protocol\V1\Result;
use SplitIO\ThinSdk\Foundation\Lang\Enforce;

class SplitNamesResponse extends Response
{

    private /*array*/ $splitNames;

    public function __construct(Result $status, array $splitNames)
    {
        parent::__construct($status);
        $this->splitNames = $splitNames;
    }

    public function getSplitNames(): array
    {
        return $this->splitNames;
    }

    public static function fromRaw(/*array*/$raw): SplitNamesResponse
    {
        Enforce::isArray($raw);
        $payload = Enforce::isArray($raw['p']);
        return new SplitNamesResponse(Result::from(Enforce::isInt($raw['s'])), Enforce::isArray($payload['n']));
    }
}
