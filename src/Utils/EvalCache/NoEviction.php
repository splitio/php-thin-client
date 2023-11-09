<?php

namespace SplitIO\ThinSdk\Utils\EvalCache;

class NoEviction implements EvictionPolicy
{

    private /*int*/  $maxSize;

    public function __construct(int $maxSize)
    {
        $this->maxSize = max(0, $maxSize);
    }

    public function postCacheInsertionHook(string $newKey, array &$data)
    {
        if ($this->maxSize > 0 && count($data) >= $this->maxSize) {
            unset($data[$newKey]);
        }
    }
}
