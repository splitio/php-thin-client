<?php

namespace SplitIO\Test\Link\Protocol\V1;


use SplitIO\ThinSdk\Link\Protocol\V1\Result;
use SplitIO\ThinSdk\Link\Protocol\V1\TrackResponse;

use PHPUnit\Framework\TestCase;

class TrackResponseTest extends TestCase
{

    public function testParsingHappyPaths(): void
    {

        $raw = ['s' => 0x01, 'p' => ['s' => true]];
        $this->assertEquals(new TrackResponse(Result::Ok(), true), TrackResponse::fromRaw($raw));

        $raw = ['s' => 0x01, 'p' => ['s' => false]];
        $this->assertEquals(new TrackResponse(Result::Ok(), false), TrackResponse::fromRaw($raw));
    }

    public function testParsingNonIntStatus(): void
    {
        $this->expectExceptionMessageMatches("/^expected an int .*/");
        TrackResponse::fromRaw(['s' => [], 'p' => []]);
    }

    public function testParsingNonArrayPayload(): void
    {
        $this->expectExceptionMessageMatches("/^expected an array .*/");
        TrackResponse::fromRaw(['s' => 0x01, 'p' => 1]);
    }
}
