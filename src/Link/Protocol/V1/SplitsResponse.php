<?php

namespace SplitIO\ThinSdk\Link\Protocol\V1;

use SplitIO\ThinSdk\Link\Protocol\V1\Result;
use SplitIO\ThinSdk\Foundation\Lang\Enforce;

class SplitsResponse extends Response
{

    private /*array*/ $splitViews;

    public function __construct(Result $status, array $splitViews)
    {
        parent::__construct($status);
        $this->splitViews = $splitViews;
    }

    public function getViews(): array
    {
        return $this->splitViews;
    }

    public static function fromRaw(/*array*/$raw): SplitsResponse
    {
        Enforce::isArray($raw);
        $payload = Enforce::isArray($raw['p']);
        return new SplitsResponse(
            Result::from(Enforce::isInt($raw['s'])),
            array_map(function ($e) {
                return SplitViewResult::fromRaw(Enforce::isArray($e));
            }, Enforce::isArray($payload['s']))
        );
    }
}
