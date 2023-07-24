<?php

namespace SplitIO\Test\Link\Consumer;

use SplitIO\ThinSdk\Config\Transfer;

use PHPUnit\Framework\TestCase;

class TransferTest extends TestCase
{

    public function testConfigDefault()
    {
        $cfg = Transfer::default();
        $this->assertEquals('/var/run/splitd.sock', $cfg->sockFN());
        $this->assertEquals('unix-seqpacket', $cfg->connType());
        $this->assertEquals(null, $cfg->bufferSize());
        $this->assertEquals(null, $cfg->timeout());
    }

    public function testConfigParsing()
    {
        $cfg = Transfer::fromArray([
                'address' => 'someAddress',
                'type' => 'unix-stream',
                'timeout' => 123,
                'bufferSize' => 1234,
            ]);

        $this->assertEquals('someAddress', $cfg->sockFN());
        $this->assertEquals('unix-stream', $cfg->connType());
        $this->assertEquals(123, $cfg->timeout());
        $this->assertEquals(1234, $cfg->bufferSize());
    }
}
