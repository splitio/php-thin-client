<?php

namespace SplitIO\ThinSdk\Utils\EvalCache;

interface EvictionPolicy
{
    public function postCacheInsertionHook(string $newKey, array &$data);
}
