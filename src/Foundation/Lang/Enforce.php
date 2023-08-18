<?php

namespace SplitIO\ThinSdk\Foundation\Lang;

class Enforce
{
    static function isInt($in, ?string $msg = null): int
    {
        if (!is_int($in)) {
            throw new \Exception($msg ?? ("expected an int got a " . gettype($in)));
        }
        return $in;
    }

    static function isString($in, ?string $msg = null): string
    {
        if (!is_string($in)) {
            throw new \Exception($msg ?? ("expected a string got a " . gettype($in)));
        }
        return $in;
    }

    static function isArray($in, ?string $msg = null): array
    {
        if (!is_array($in)) {
            throw new \Exception($msg ?? ("expected an array got a " . gettype($in)));
        }
        return $in;
    }

    static function isBool($in, ?string $msg = null): bool
    {
        if (!is_bool($in)) {
            throw new \Exception($msg ?? ("expected a bool, got a " . gettype($in)));
        }
        return $in;
    }
}
