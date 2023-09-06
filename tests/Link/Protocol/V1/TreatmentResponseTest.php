<?php

namespace SplitIO\Test\Link\Protocol\V1;


use SplitIO\ThinSdk\Link\Protocol\V1\Result;
use SplitIO\ThinSdk\Link\Protocol\V1\TreatmentResponse;
use SplitIO\ThinSdk\Link\Protocol\V1\EvaluationResult;
use SplitIO\ThinSdk\Link\Protocol\V1\ImpressionListenerData;

use PHPUnit\Framework\TestCase;

class TreatmentResponseTest extends TestCase
{

    public function testParsingHappyPaths(): void
    {

        $raw = ['s' => 0x01, 'p' => ['t' => 'on', 'l' => ['l' => 'label1', 'c' => 123, 'm' => 456], 'c' => 'cfg']];
        $this->assertEquals(
            new TreatmentResponse(Result::Ok(), new EvaluationResult('on', new ImpressionListenerData('label1', 123, 456), 'cfg')),
            TreatmentResponse::fromRaw($raw)
        );

        $raw = ['s' => 0x01, 'p' => ['t' => 'on', 'l' => ['l' => 'label1', 'c' => 123, 'm' => 456]]];
        $this->assertEquals(
            new TreatmentResponse(Result::Ok(), new EvaluationResult('on', new ImpressionListenerData('label1', 123, 456), null)),
            TreatmentResponse::fromRaw($raw)
        );

        $raw = ['s' => 0x01, 'p' => ['t' => 'on']];
        $this->assertEquals(
            new TreatmentResponse(Result::Ok(), new EvaluationResult('on', null, null)),
            TreatmentResponse::fromRaw($raw)
        );

    }

    public function testParsingNonIntStatus(): void
    {
        $this->expectExceptionMessageMatches("/^expected an int .*/");
        TreatmentResponse::fromRaw(['s' => 'someStr']);
    }

    public function testParsingNonArrayPayload(): void
    {
        $this->expectExceptionMessageMatches("/^expected an array .*/");
        TreatmentResponse::fromRaw(['s' => 0x01, 'p' => true]);
    }
}
