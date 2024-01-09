<?php

namespace SplitIO\Test\Link\Protocol\V1;


use SplitIO\ThinSdk\Link\Protocol\V1\Result;
use SplitIO\ThinSdk\Link\Protocol\V1\SplitResponse;
use SplitIO\ThinSdk\Link\Protocol\V1\SplitViewResult;

use PHPUnit\Framework\TestCase;

class SplitResponseTest extends TestCase
{

    public function testParsingHappyPaths(): void
    {
        $raw = ['s' => 0x01, 'p' => [
            'n' => 'someName',
            't' => 'someTrafficType',
            'k' => true,
            's' => ['on', 'off'],
            'c' => 123,
            'd' => 'on',
            'f' => ['on' => 'some'],
            'e' => ['s1', 's2'],
        ]];
        $this->assertEquals(
            new SplitResponse(Result::Ok(), new SplitViewResult("someName", "someTrafficType", true, ['on', 'off'], 123, 'on', ['s1', 's2'], ['on' => 'some'])),
            SplitResponse::fromRaw($raw)
        );

        $raw['p']['e'] = [];
        $this->assertEquals(
            new SplitResponse(Result::Ok(), new SplitViewResult("someName", "someTrafficType", true, ['on', 'off'], 123, 'on', [],['on' => 'some'])),
            SplitResponse::fromRaw($raw)
        );

        $this->assertEquals(new SplitResponse(Result::Ok(), null), SplitResponse::fromRaw(['s' => 0x01, 'p' => null]));
    }

    public function testParsingNonIntStatus(): void
    {
        $this->expectExceptionMessageMatches("/^expected an int .*/");
        SplitResponse::fromRaw(['s' => [], 'p' => []]);
    }

    public function testParsingNonArrayPayload(): void
    {
        $this->expectExceptionMessageMatches("/^expected an array .*/");
        SplitResponse::fromRaw(['s' => 0x01, 'p' => 1]);
    }

    public function testParsingNonArraySplitList(): void
    {
        $this->expectExceptionMessageMatches("/^expected a string .*/");
        SplitResponse::fromRaw(['s' => 0x01, 'p' => ['n' => 3]]);
    }
}
