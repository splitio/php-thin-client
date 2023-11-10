<?php

namespace SplitIO\ThinSdk\Utils\EvalCache;

class KeyAttributeCRC32Hasher implements InputHasher
{
    public function hashInput(string $key, string $feature, ?array $attributes = null): string
    {
        // based on https://grechin.org/2021/04/06/php-json-encode-vs-serialize-performance-comparison.html
        // php `serialize` is slower for encoding but faster for decoding.
        // Since this we never decode here (we're just serializing for hashing purposes),
        // it makes sense to go with JSON
        $prefix = $key . "::" . $feature;
        return is_null($attributes) ? $prefix : $prefix . "::" .  crc32(json_encode($attributes));
    }
}
