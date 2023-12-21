<?php

namespace SplitIO\Test\Link\Consumer;

use SplitIO\ThinSdk\Config\Tracer;
use SplitIO\ThinSdk\Utils\Tracing\TracerHook;

use PHPUnit\Framework\TestCase;

class TraceTest extends TestCase
{

    public function testConfigDefault()
    {
        $cfg = Tracer::default();
        $this->assertEquals(null, $cfg->hook());
        $this->assertEquals(false, $cfg->forwardArgs());
    }

    public function testConfigParsing()
    {
        $tMock = $this->createMock(TracerHook::class);
        $cfg = Tracer::fromArray([
            'hook' => $tMock,
            'forwardArgs' => true,
        ]);

        $this->assertEquals($tMock, $cfg->hook());
        $this->assertEquals(true, $cfg->forwardArgs());
    }
}
