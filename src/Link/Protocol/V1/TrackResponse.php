<?php

namespace SplitIO\ThinSdk\Link\Protocol\V1;

use SplitIO\ThinSdk\Link\Protocol\V1\Result;
use SplitIO\ThinSdk\Foundation\Lang\Enforce;

class TrackResponse extends Response
{

    private $success;

    public function __construct(Result $status, bool $success)
    {
        parent::__construct($status);
        $this->success = $success;
    }

    public function getSuccess(): bool
    {
        return $this->success;
    }

    static function fromRaw(/*mixed*/ $raw)/*: mixed*/
    {
        $raw = Enforce::isArray($raw);
        $payload = Enforce::isArray($raw['p']);
        return new TrackResponse(
            Result::from(Enforce::isInt($raw['s'])),
            Enforce::isBool($payload['s']));
    }
}
