<?php

namespace SplitIO\ThinClient\Foundation\Enum;

class EnumValue
{
    private $value;

    public function __construct(int $value)
    {
        $this->value = $value;
    }

    public function get(): int
    {
        return $this->value;
    }
}