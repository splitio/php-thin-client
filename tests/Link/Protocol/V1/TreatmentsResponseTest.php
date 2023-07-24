<?php

namespace SplitIO\Test\Link\Protocol\V1;


use SplitIO\ThinSdk\Link\Protocol\V1\Result;
use SplitIO\ThinSdk\Link\Protocol\V1\TreatmentsResponse;
use SplitIO\ThinSdk\Link\Protocol\V1\EvaluationResult;
use SplitIO\ThinSdk\Link\Protocol\V1\ImpressionListenerData;

use PHPUnit\Framework\TestCase;

class TreatmentsResponseTest extends TestCase
{

    public function testParsingHappyPaths(): void
    {

        $raw = ['s' => 0x01, 'p' => ['r' => [
            ['t' => 'on', 'l' => ['l' => 'label1', 'c' => 123, 'm' => 456], 'c' => 'cfg'],
            ['t' => 'off', 'l' => ['l' => 'label2', 'c' => 124, 'm' => 457]],
            ['t' => 'na', 'c' => 'cfg2'],
            ['t' => 'pepe'],
        ]]];
        $this->assertEquals(
            new TreatmentsResponse(Result::Ok(), [
                new EvaluationResult('on', new ImpressionListenerData('label1', 123, 456), 'cfg'),
                new EvaluationResult('off', new ImpressionListenerData('label2', 124, 457), null),
                new EvaluationResult('na', null, 'cfg2'),
                new EvaluationResult('pepe', null, null),
            ]),
            TreatmentsResponse::fromRaw($raw)
        );
    }

    public function testParsingNonIntStatus(): void
    {
        $this->expectExceptionMessageMatches("/^expected an int .*/");
        TreatmentsResponse::fromRaw(['s' => 'someStr']);
    }
    
    public function testParsingNonArrayPayload(): void
    {
        $this->expectExceptionMessageMatches("/^expected an array .*/");
        TreatmentsResponse::fromRaw(['s' => 0x01, 'p' => [ 'r' => true]]);
    }
}
