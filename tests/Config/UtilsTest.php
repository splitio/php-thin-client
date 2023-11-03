<?php

namespace SplitIO\Test\Link\Consumer;

use SplitIO\ThinSdk\Config\Utils;
use SplitIO\ThinSdk\Utils\ImpressionListener;
use SplitIO\ThinSdk\Utils\EvalCache\InputHasher;

use PHPUnit\Framework\TestCase;

class UtilsTest extends TestCase
{

    public function testConfigDefault()
    {
        $cfg = Utils::default();
        $this->assertEquals(null, $cfg->impressionListener());
        $this->assertEquals('none', $cfg->evaluationCache());
        $this->assertEquals(null, $cfg->customCacheHash());
    }

    public function testConfigParsing()
    {
        $ilMock = $this->createMock(ImpressionListener::class);
        $ihMock = $this->createMock(InputHasher::class);
        $cfg = Utils::fromArray([
            'impressionListener' => $ilMock,
            'evaluationCache' => 'custom',
            'customCacheHash' => $ihMock,
        ]);

        $this->assertEquals($ilMock, $cfg->impressionListener());
        $this->assertEquals('custom', $cfg->evaluationCache());
        $this->assertEquals($ihMock, $cfg->customCacheHash());
    }
}
