<?php

namespace SplitIO\Test\Link\Consumer;

use SplitIO\ThinSdk\Config\Serialization;

use PHPUnit\Framework\TestCase;

class SerializationTest extends TestCase
{

    public function testConfigDefault()
    {
        $cfg = Serialization::default();
        $this->assertEquals('msgpack', $cfg->mechanism());
    }

    public function testConfigParsing()
    {
        $cfg = Serialization::fromArray(['mechanism' => 'msgpack']);
        $this->assertEquals('msgpack', $cfg->mechanism());
    }
}
