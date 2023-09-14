<?php

namespace SplitIO\Test\Link\Protocol\V1;


use SplitIO\ThinSdk\Link\Protocol\V1\Result;
use SplitIO\ThinSdk\Link\Protocol\V1\SplitNamesResponse;

use PHPUnit\Framework\TestCase;

class SplitNamesResponseTest extends TestCase
{

    public function testParsingHappyPaths(): void
    {

        $raw = ['s' => 0x01, 'p' => ['n' => ["s1", "s2"]]];
        $this->assertEquals(new SplitNamesResponse(Result::Ok(), ["s1", "s2"]), SplitNamesResponse::fromRaw($raw));

        $raw = ['s' => 0x01, 'p' => ['n' => []]];
        $this->assertEquals(new SplitNamesResponse(Result::Ok(), []), SplitNamesResponse::fromRaw($raw));
    }

    public function testParsingNonIntStatus(): void
    {
        $this->expectExceptionMessageMatches("/^expected an int .*/");
        SplitNamesResponse::fromRaw(['s' => [], 'p' => []]);
    }

    public function testParsingNonArrayPayload(): void
    {
        $this->expectExceptionMessageMatches("/^expected an array .*/");
        SplitNamesResponse::fromRaw(['s' => 0x01, 'p' => 1]);
    }

    public function testParsingNonArraySplitList(): void
    {
        $this->expectExceptionMessageMatches("/^expected an array .*/");
        SplitNamesResponse::fromRaw(['s' => 0x01, 'p' => ['n' => 3]]);
    }
}
