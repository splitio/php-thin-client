<?php

namespace SplitIO\ThinSdk\Fallback;

class FallbackDisabledException extends \Exception
{

    private /*\Exception*/ $wrapped;

    public function __construct(\Exception $wrapped)
    {
        $this->wrapped = $wrapped;
    }

    public function wrapped(): \Exception
    {
        return $this->wrapped;
    }
}
