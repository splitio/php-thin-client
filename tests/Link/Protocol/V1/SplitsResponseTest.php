<?php

namespace SplitIO\Test\Link\Protocol\V1;


use SplitIO\ThinSdk\Link\Protocol\V1\Result;
use SplitIO\ThinSdk\Link\Protocol\V1\SplitsResponse;
use SplitIO\ThinSdk\Link\Protocol\V1\SplitViewResult;

use PHPUnit\Framework\TestCase;

class SplitsResponseTest extends TestCase
{

    public function testParsingHappyPaths(): void
    {

        $raw = [
            's' => 0x01,
            'p' => ['s' => [
                ['n' => 's1', 't' => 'someTrafficType', 'k' => true, 's' => ['on', 'off'], 'c' => 123, 'f' => ['on' => 'some']],
                ['n' => 's2', 't' => 'someTrafficType', 'k' => false, 's' => ['on', 'off'], 'c' => 124, 'f' => null],
            ]]
        ];
        $this->assertEquals(
            new SplitsResponse(Result::Ok(), [
                new SplitViewResult("s1", "someTrafficType", true, ['on', 'off'], 123, ['on' => 'some']),
                new SplitViewResult("s2", "someTrafficType", false, ['on', 'off'], 124, null),
            ]),
            SplitsResponse::fromRaw($raw)
        );

        $this->assertEquals(new SplitsResponse(Result::Ok(), []), SplitsResponse::fromRaw(['s' => 0x01, 'p' => ['s' => []]]));
    }

    public function testParsingNonIntStatus(): void
    {
        $this->expectExceptionMessageMatches("/^expected an int .*/");
        SplitsResponse::fromRaw(['s' => [], 'p' => []]);
    }

    public function testParsingNonArrayPayload(): void
    {
        $this->expectExceptionMessageMatches("/^expected an array .*/");
        SplitsResponse::fromRaw(['s' => 0x01, 'p' => 1]);
    }

    public function testParsingNonArraySplitList(): void
    {
        $this->expectExceptionMessageMatches("/^expected an array .*/");
        SplitsResponse::fromRaw(['s' => 0x01, 'p' => ['s' => 3]]);
    }
}
