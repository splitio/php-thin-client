<?php

namespace SplitIO\Test\Link\Protocol\V1;


use SplitIO\ThinSdk\Link\Protocol\V1\Result;
use SplitIO\ThinSdk\Link\Protocol\V1\TreatmentsByFlagSetResponse;
use SplitIO\ThinSdk\Link\Protocol\V1\EvaluationResult;
use SplitIO\ThinSdk\Link\Protocol\V1\ImpressionListenerData;

use PHPUnit\Framework\TestCase;

class TreatmentsByFlagSetResponseTest extends TestCase
{

    public function testParsingHappyPaths(): void
    {

        $raw = ['s' => 0x01, 'p' => ['r' => [
            'f1' => ['t' => 'on', 'l' => ['l' => 'label1', 'c' => 123, 'm' => 456], 'c' => 'cfg'],
            'f2' => ['t' => 'off', 'l' => ['l' => 'label2', 'c' => 124, 'm' => 457]],
            'f3' => ['t' => 'na', 'c' => 'cfg2'],
            'f4' => ['t' => 'pepe'],
        ]]];

        $this->assertEquals(
            new TreatmentsByFlagSetResponse(Result::Ok(), [
                'f1' => new EvaluationResult('on', new ImpressionListenerData('label1', 123, 456), 'cfg'),
                'f2' =>  new EvaluationResult('off', new ImpressionListenerData('label2', 124, 457), null),
                'f3' => new EvaluationResult('na', null, 'cfg2'),
                'f4' => new EvaluationResult('pepe', null, null),
            ]),
            TreatmentsByFlagSetResponse::fromRaw($raw)
        );
    }

    public function testParsingNonIntStatus(): void
    {
        $this->expectExceptionMessageMatches("/^expected an int .*/");
        TreatmentsByFlagSetResponse::fromRaw(['s' => 'someStr']);
    }
    
    public function testParsingNonArrayPayload(): void
    {
        $this->expectExceptionMessageMatches("/^expected an array .*/");
        TreatmentsByFlagSetResponse::fromRaw(['s' => 0x01, 'p' => [ 'r' => true]]);
    }
}
