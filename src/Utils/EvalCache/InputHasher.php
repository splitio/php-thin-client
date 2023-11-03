<?php

namespace SplitIO\ThinSdk\Utils\EvalCache;

interface InputHasher
{
    public function hashInput(string $key, string $feature, ?array $attributes = null): string;
}
