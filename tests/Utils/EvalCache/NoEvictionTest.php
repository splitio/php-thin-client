<?php

namespace SplitIO\Test\Utils\EvalCache;

use SplitIO\ThinSdk\Utils\EvalCache\NoEviction;
use PHPUnit\Framework\TestCase;

class NoEvictionTest extends TestCase
{
    public function testNoEvictionPolicy()
    {
        $policy = new NoEviction(0);
        $arr = ['a', 'b', 'c'];
        $policy->postCacheInsertionHook("key", $arr);
        $this->assertEquals(3, count($arr));
    }
}
