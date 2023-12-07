<?php

namespace SplitIO\Test\Utils\EvalCache;

use SplitIO\ThinSdk\Utils\EvalCache\KeyAttributeCRC32Hasher;
use PHPUnit\Framework\TestCase;

class KeyAttributeCRC32HasherTest extends TestCase
{

    public function testHashNoAttributes()
    {
        $hasher = new KeyAttributeCRC32Hasher();
        $this->assertEquals("key::feature", $hasher->hashInput("key", "feature"));
        $this->assertEquals("key::feature", $hasher->hashInput("key", "feature", null));
    }

    public function testHashWithAttributes()
    {
        $testCases = [
            [],
            ['a' => 1],
            ['b' => 'asd'],
            ['c' => ['a', 'b', 'à']],
            ['d' => null],
        ];

        $hasher = new KeyAttributeCRC32Hasher();
        foreach ($testCases as $testCase) {
            $this->assertEquals("key::feature::" . crc32(json_encode($testCase)), $hasher->hashInput("key", "feature", $testCase));
        }
    }
}
