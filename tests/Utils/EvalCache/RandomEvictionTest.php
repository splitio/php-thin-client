<?php

namespace SplitIO\Test\Utils\EvalCache;

use SplitIO\ThinSdk\Utils\EvalCache\RandomEviction;
use PHPUnit\Framework\TestCase;

class RandomEvictionTest extends TestCase
{
    public function testRandomEvictionPolicy()
    {
        $policy = new RandomEviction(1);
        $data = [
            'e' => 5,
            'key' => 6,
        ];
        $policy->postCacheInsertionHook("key", $data);
        $this->assertEquals(1, count($data));
        $this->assertArrayHasKey('key', $data);
    }
}
