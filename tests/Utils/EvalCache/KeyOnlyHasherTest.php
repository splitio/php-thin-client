<?php

namespace SplitIO\Test\Utils\EvalCache;

use SplitIO\ThinSdk\Utils\EvalCache\KeyOnlyHasher;
use PHPUnit\Framework\TestCase;

class KeyOnlyHasherTest extends TestCase
{

    public function testHash()
    {
        $hasher = new KeyOnlyHasher();
        $this->assertEquals("key::feature", $hasher->hashInput("key", "feature"));
        $this->assertEquals("key::feature", $hasher->hashInput("key", "feature", null));
        $this->assertEquals("key::feature", $hasher->hashInput("key", "feature", []));
        $this->assertEquals("key::feature", $hasher->hashInput("key", "feature", ['a' => 1]));
    }
}
