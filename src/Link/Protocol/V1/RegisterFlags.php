<?php

namespace SplitIO\ThinSdk\Link\Protocol\V1;

class RegisterFlags
{
    
    const FEEDBACK_IMPRESSIONS = 0;

    private /*int*/ $flags = 0;

    public function __construct(bool $feedbackImpressions)
    {
        if ($feedbackImpressions) {
            $this->flags = (1 << self::FEEDBACK_IMPRESSIONS);
        }
    }

    public function get(): int
    {
        return $this->flags;
    }
}
