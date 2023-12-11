<?php

namespace SplitIO\Test\Link\Consumer;

use SplitIO\ThinSdk\Config\Utils;
use SplitIO\ThinSdk\Config\EvaluationCache;
use SplitIO\ThinSdk\Config\Tracer;
use SplitIO\ThinSdk\Utils\ImpressionListener;
use SplitIO\ThinSdk\Utils\EvalCache\InputHasher;
use SplitIO\ThinSdk\Utils\Tracing\TracerHook;

use PHPUnit\Framework\TestCase;

class UtilsTest extends TestCase
{

    public function testConfigDefault()
    {
        $cfg = Utils::default();
        $this->assertEquals(null, $cfg->impressionListener());
        $this->assertEquals(EvaluationCache::default(), $cfg->evaluationCache());
        $this->assertEquals(Tracer::default(), $cfg->tracer());
    }

    public function testConfigParsing()
    {
        $ilMock = $this->createMock(ImpressionListener::class);
        $ihMock = $this->createMock(InputHasher::class);
        $tMock = $this->createMock(TracerHook::class);
        $cfg = Utils::fromArray([
            'impressionListener' => $ilMock,
            'evaluationCache' => [
                'type' => 'key-attributes',
                'evictionPolicy' => 'random',
                'maxSize' => 1234,
                'customHash' => $ihMock,
            ],
            '__tracer' => [
                'hook' => $tMock,
                'forwardArgs' => true,
            ],
        ]);

        $this->assertEquals($ilMock, $cfg->impressionListener());
        $this->assertEquals('key-attributes', $cfg->evaluationCache()->type());
        $this->assertEquals('random', $cfg->evaluationCache()->evictionPolicy());
        $this->assertEquals($ihMock, $cfg->evaluationCache()->customHash());
        $this->assertEquals(1234, $cfg->evaluationCache()->maxSize());
        $this->assertEquals($tMock, $cfg->tracer()->hook());
        $this->assertEquals(true, $cfg->tracer()->forwardArgs());
    }
}
