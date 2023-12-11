<?php

namespace SplitIO\Test\Utils\Tracing;

use SplitIO\ThinSdk\Utils\Tracing\Tracer;
use SplitIO\ThinSdk\Utils\Tracing\TracingEventFactory as TEF;

use PHPUnit\Framework\TestCase;

class TracingEventFactoryTest extends TestCase
{

    public function testTEF()
    {
        $this->assertEquals(
            ['id' => '123', 'method' => Tracer::METHOD_GET_TREATMENT, 'event' => Tracer::EVENT_START, 'arguments' => ['a', 'b', 'c']],
            TEF::forStart(Tracer::METHOD_GET_TREATMENT, '123', ['a', 'b', 'c']),
        );

        $this->assertEquals(
            ['id' => '123', 'method' => Tracer::METHOD_GET_TREATMENTS, 'event' => Tracer::EVENT_RPC_START],
            TEF::forRPCStart(Tracer::METHOD_GET_TREATMENTS, '123'),
        );

        $this->assertEquals(
            ['id' => '123', 'method' => Tracer::METHOD_GET_TREATMENT_WITH_CONFIG, 'event' => Tracer::EVENT_RPC_END],
            TEF::forRPCEnd(Tracer::METHOD_GET_TREATMENT_WITH_CONFIG, '123'),
        );

        $this->assertEquals(
            ['id' => '123', 'method' => Tracer::METHOD_GET_TREATMENTS_WITH_CONFIG, 'event' => Tracer::EVENT_END],
            TEF::forEnd(Tracer::METHOD_GET_TREATMENTS_WITH_CONFIG, '123'),
        );

        $this->assertEquals(
            ['id' => '123', 'method' => Tracer::METHOD_TRACK, 'event' => Tracer::EVENT_EXCEPTION, 'exception' => new \Exception("sarasa")],
            TEF::forException(Tracer::METHOD_TRACK, '123', new \Exception("sarasa")),
        );
    }
}
