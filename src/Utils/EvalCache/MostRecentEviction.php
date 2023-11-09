<?php

namespace SplitIO\ThinSdk\Utils\EvalCache;

class MostRecentEviction implements EvictionPolicy
{

    private /*int*/ $maxSize;

    public function __construct(int $size)
    {
        $this->maxSize = $size;
    }

    public function postCacheInsertionHook(string $key, array &$data)
    {
        if (count($data) > $this->maxSize) {
            unset($data[$key]);
        }
    }
}
