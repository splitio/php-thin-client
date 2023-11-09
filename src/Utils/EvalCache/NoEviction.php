<?php

namespace SplitIO\ThinSdk\Utils\EvalCache;

class NoEviction implements EvictionPolicy
{
    public function postCacheInsertionHook(string $newKey, array &$data)
    {
    }
}
