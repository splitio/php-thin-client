<?php

namespace SplitIO\ThinClient\Foundation\Enum;

class C73
{
    private $value;

    public function __construct(int $value)
    {
        $this->value = $value;
    }

    public function value(): int
    {
        return $this->value;
    }

}
