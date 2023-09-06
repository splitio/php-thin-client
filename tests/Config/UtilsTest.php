<?php

namespace SplitIO\Test\Link\Consumer;

use SplitIO\ThinSdk\Config\Utils;

use SplitIO\ThinSdk\Utils\ImpressionListener;

use PHPUnit\Framework\TestCase;

class UtilsTest extends TestCase
{

    public function testConfigDefault()
    {
        $cfg = Utils::default();
        $this->assertEquals(null, $cfg->impressionListener());
    }

    public function testConfigParsing()
    {
        $ilMock = $this->createMock(ImpressionListener::class);
        $cfg = Utils::fromArray([
            'impressionListener' => $ilMock,
        ]);

        $this->assertEquals($ilMock, $cfg->impressionListener());
    }
}
