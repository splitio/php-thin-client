<?php

namespace SplitIO\Test\Link\Consumer;

use SplitIO\ThinSdk\Config\Utils;
use SplitIO\ThinSdk\Config\EvaluationCache;
use SplitIO\ThinSdk\Utils\ImpressionListener;
use SplitIO\ThinSdk\Utils\EvalCache\InputHasher;

use PHPUnit\Framework\TestCase;

class UtilsTest extends TestCase
{

    public function testConfigDefault()
    {
        $cfg = Utils::default();
        $this->assertEquals(null, $cfg->impressionListener());
        $this->assertEquals(EvaluationCache::default(), $cfg->evaluationCache());
    }

    public function testConfigParsing()
    {
        $ilMock = $this->createMock(ImpressionListener::class);
        $ihMock = $this->createMock(InputHasher::class);
        $cfg = Utils::fromArray([
            'impressionListener' => $ilMock,
            'evaluationCache' => [
                'type' => 'key-attributes',
                'evictionPolicy' => 'random',
                'maxSize' => 1234,
                'customHash' => $ihMock,
            ],
        ]);

        $this->assertEquals($ilMock, $cfg->impressionListener());
        $this->assertEquals('key-attributes', $cfg->evaluationCache()->type());
        $this->assertEquals('random', $cfg->evaluationCache()->evictionPolicy());
        $this->assertEquals($ihMock, $cfg->evaluationCache()->customHash());
        $this->assertEquals(1234, $cfg->evaluationCache()->maxSize());
    }
}
