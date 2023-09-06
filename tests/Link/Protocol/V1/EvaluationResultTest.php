<?php

namespace SplitIO\Test\Link\Protocol\V1;


use SplitIO\ThinSdk\Link\Protocol\V1\EvaluationResult;
use SplitIO\ThinSdk\Link\Protocol\V1\ImpressionListenerData;

use PHPUnit\Framework\TestCase;

class EvaluationResultTest extends TestCase
{

    public function testParsingHappyPaths(): void
    {
        $this->assertEquals(new EvaluationResult("on", null, null), EvaluationResult::fromRaw(['t' => 'on']));

        $ilDataRaw = ['l' => 'label1', 'c' => 123, 'm' => 456];
        $this->assertEquals(
            new EvaluationResult("on", ImpressionListenerData::fromRaw($ilDataRaw), null),
            EvaluationResult::fromRaw(['t' => 'on', 'l' => $ilDataRaw])
        );

        $this->assertEquals(new EvaluationResult("on", null, 'sarasa'), EvaluationResult::fromRaw(['t' => 'on', 'c' => 'sarasa']));
    }

    public function testParsingNonStringTreatment(): void
    {
        $this->expectExceptionMessageMatches("/^expected a string .*/");
        EvaluationResult::fromRaw(['t' => 123]);
    }

    public function testParsingNonArrayListenerData(): void
    {
        $this->expectExceptionMessageMatches("/^expected an array .*/");
        EvaluationResult::fromRaw(['t' => 'on', 'l' => true]);
    }

    public function testParsingNonStringConfig(): void
    {
        $this->expectExceptionMessageMatches("/^expected a string .*/");
        EvaluationResult::fromRaw(['t' => 'on', 'c' => true]);
    }

}
