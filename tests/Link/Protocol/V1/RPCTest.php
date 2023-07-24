<?php

namespace SplitIO\Test\Link\Protocol\V1;

use SplitIO\ThinSdk\Link\Protocol\Version;
use SplitIO\ThinSdk\Link\Protocol\V1\RPC;
use SplitIO\ThinSdk\Link\Protocol\V1\OpCode;
use SplitIO\ThinSdk\Link\Protocol\V1\TreatmentArgs;
use SplitIO\ThinSdk\Link\Protocol\V1\TreatmentsArgs;


use PHPUnit\Framework\TestCase;

class RPCTest extends TestCase
{

    public function testTreatmentRPC(): void
    {
        $dt = new \DateTime('now');
        $rpc = RPC::forTreatment('key1', 'buck', 'feature1', ['a' => 'sarasa', 'b' => 2, 'c' => ['q', 'w'], 'd' => $dt]);
        $this->assertEquals(OpCode::Treatment(), $rpc->getOpCode());
        $this->assertEquals(Version::V1(), $rpc->getVersion());
        $this->assertEquals('key1', $rpc->getArgs()[TreatmentArgs::KEY()->getValue()]);
        $this->assertEquals('buck', $rpc->getArgs()[TreatmentArgs::BUCKETING_KEY()->getValue()]);
        $this->assertEquals('feature1', $rpc->getArgs()[TreatmentArgs::FEATURE()->getValue()]);
        $this->assertEquals('sarasa', $rpc->getArgs()[TreatmentArgs::ATTRIBUTES()->getValue()]['a']);
        $this->assertEquals(2, $rpc->getArgs()[TreatmentArgs::ATTRIBUTES()->getValue()]['b']);
        $this->assertEquals(['q', 'w'], $rpc->getArgs()[TreatmentArgs::ATTRIBUTES()->getValue()]['c']);
        $this->assertEquals($dt, $rpc->getArgs()[TreatmentArgs::ATTRIBUTES()->getValue()]['d']);
        $this->assertEquals(
            ['v' => 1, 'o' => 0x11, 'a' => ['key1', 'buck', 'feature1', ['a' => 'sarasa', 'b' => 2, 'c' => ['q', 'w'], 'd' => $dt]]],
            $rpc->getSerializable()
        );
    }

    public function testTreatmentsRPC(): void
    {
        $dt = new \DateTime('now');
        $rpc = RPC::forTreatments('key1', 'buck', ['f1', 'f2'], ['a' => 'sarasa', 'b' => 2, 'c' => ['q', 'w'], 'd' => $dt]);
        $this->assertEquals(OpCode::Treatments(), $rpc->getOpCode());
        $this->assertEquals(Version::V1(), $rpc->getVersion());
        $this->assertEquals('key1', $rpc->getArgs()[TreatmentsArgs::KEY()->getValue()]);
        $this->assertEquals('buck', $rpc->getArgs()[TreatmentsArgs::BUCKETING_KEY()->getValue()]);
        $this->assertEquals(['f1', 'f2'], $rpc->getArgs()[TreatmentsArgs::FEATURES()->getValue()]);
        $this->assertEquals('sarasa', $rpc->getArgs()[TreatmentsArgs::ATTRIBUTES()->getValue()]['a']);
        $this->assertEquals(2, $rpc->getArgs()[TreatmentsArgs::ATTRIBUTES()->getValue()]['b']);
        $this->assertEquals(['q', 'w'], $rpc->getArgs()[TreatmentsArgs::ATTRIBUTES()->getValue()]['c']);
        $this->assertEquals($dt, $rpc->getArgs()[TreatmentsArgs::ATTRIBUTES()->getValue()]['d']);
        $this->assertEquals(
            ['v' => 1, 'o' => 0x12, 'a' => ['key1', 'buck', ['f1', 'f2'], ['a' => 'sarasa', 'b' => 2, 'c' => ['q', 'w'], 'd' => $dt]]],
            $rpc->getSerializable()
        );
    }

}
