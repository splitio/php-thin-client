<?php

namespace SplitIO\Test\Utils\EvalCache;

use SplitIO\ThinSdk\Utils\EvalCache\MostRecentEviction;
use PHPUnit\Framework\TestCase;

class MostRecentEvictionTest extends TestCase
{
    public function testRandomEvictionPolicy()
    {
        $policy = new MostRecentEviction(1);
        $data = [
            'e' => 5,
            'key' => 6,
        ];
        $policy->postCacheInsertionHook("key", $data);
        $this->assertEquals(1, count($data));
        $this->assertArrayHasKey('e', $data);
        $this->assertArrayNotHasKey('key', $data);
    }
}
