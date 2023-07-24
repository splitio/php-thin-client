<?php

namespace SplitIO\Test\Link\Protocol\V1;


use SplitIO\ThinSdk\Link\Protocol\V1\ImpressionListenerData;

use PHPUnit\Framework\TestCase;

class ImpressionListenerDataTest extends TestCase
{

    public function testParsingHappyPaths(): void
    {
        $this->assertEquals(
            new ImpressionListenerData("label1", 123, 456),
            ImpressionListenerData::fromRaw(['l' => 'label1', 'c' => 123, 'm' => 456])
        );
    }

    public function testParsingNonStringLabel(): void
    {
        $this->expectExceptionMessageMatches("/^expected a string .*/");
        ImpressionListenerData::fromRaw(['l' => 999, 'c' => 123, 'm' => 456]);
    }

    public function testParsingNonIntChangeNumber(): void
    {
        $this->expectExceptionMessageMatches("/^expected an int .*/");
        ImpressionListenerData::fromRaw(['l' => 'label1', 'c' => 'frula', 'm' => 456]);
    }

    public function testParsingNonNonIntTimestamp(): void
    {
        $this->expectExceptionMessageMatches("/^expected an int .*/");
        ImpressionListenerData::fromRaw(['l' => 'label1', 'c' => 123, 'm' => 'frula']);
    }
}
