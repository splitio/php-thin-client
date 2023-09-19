<?php

namespace SplitIO\ThinSdk\Link\Protocol\V1;

use SplitIO\ThinSdk\Link\Protocol\V1\Result;
use SplitIO\ThinSdk\Foundation\Lang\Enforce;

class SplitResponse extends Response
{

    private /*array*/ $splitView;

    public function __construct(Result $status, ?SplitViewResult $splitView)
    {
        parent::__construct($status);
        $this->splitView = $splitView;
    }

    public function getView(): ?SplitViewResult
    {
        return $this->splitView;
    }

    public static function fromRaw(/*array*/$raw): SplitResponse
    {
        Enforce::isArray($raw);
        return new SplitResponse(
            Result::from(Enforce::isInt($raw['s'])),
            isset($raw['p']) ? SplitViewResult::fromRaw(Enforce::isArray($raw['p'])) : null
        );
    }
}
