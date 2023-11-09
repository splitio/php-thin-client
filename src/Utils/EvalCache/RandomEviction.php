<?php

namespace SplitIO\ThinSdk\Utils\EvalCache;

class RandomEviction implements EvictionPolicy
{

    private /*int*/ $maxSize;

    public function __construct(int $size)
    {
        $this->maxSize = $size;
    }

    public function postCacheInsertionHook(string $key, array &$data)
    {
        if (count($data) < $this->maxSize) {
            return;
        }

        // remove a random item (different from the last added)
        do {
            $toRemove = array_rand($data);
        } while ($key == $toRemove);
        unset($data[$toRemove]);
    }
}
