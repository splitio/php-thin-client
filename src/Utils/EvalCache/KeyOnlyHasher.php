<?php

namespace SplitIO\ThinSdk\Utils\EvalCache;

class KeyOnlyHasher implements InputHasher
{
    public function hashInput(string $key, string $feature, ?array $attributes = null): string
    {
        return  $key . "::" . $feature;
    }
}
