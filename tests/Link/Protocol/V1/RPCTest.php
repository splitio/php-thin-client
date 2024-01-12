<?php

namespace SplitIO\Test\Link\Protocol\V1;

use SplitIO\ThinSdk\Link\Protocol\V1\TreatmentsByFlagSetArgs;
use SplitIO\ThinSdk\Link\Protocol\V1\TreatmentsByFlagSetsArgs;
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

    public function testTreatmentsByFlagSetRPC(): void
    {
        $dt = new \DateTime('now');
        $rpc = RPC::forTreatmentsByFlagSet('key1', 'buck', 'set', ['a' => 'sarasa', 'b' => 2, 'c' => ['q', 'w'], 'd' => $dt]);
        $this->assertEquals(OpCode::TreatmentsByFlagSet(), $rpc->getOpCode());
        $this->assertEquals(Version::V1(), $rpc->getVersion());
        $this->assertEquals('key1', $rpc->getArgs()[TreatmentsByFlagSetArgs::KEY()->getValue()]);
        $this->assertEquals('buck', $rpc->getArgs()[TreatmentsByFlagSetArgs::BUCKETING_KEY()->getValue()]);
        $this->assertEquals('set', $rpc->getArgs()[TreatmentsByFlagSetArgs::FLAG_SET()->getValue()]);
        $this->assertEquals('sarasa', $rpc->getArgs()[TreatmentsByFlagSetArgs::ATTRIBUTES()->getValue()]['a']);
        $this->assertEquals(2, $rpc->getArgs()[TreatmentsByFlagSetArgs::ATTRIBUTES()->getValue()]['b']);
        $this->assertEquals(['q', 'w'], $rpc->getArgs()[TreatmentsByFlagSetArgs::ATTRIBUTES()->getValue()]['c']);
        $this->assertEquals($dt, $rpc->getArgs()[TreatmentsByFlagSetArgs::ATTRIBUTES()->getValue()]['d']);
        $this->assertEquals(
            ['v' => 1, 'o' => 0x15, 'a' => ['key1', 'buck', 'set', ['a' => 'sarasa', 'b' => 2, 'c' => ['q', 'w'], 'd' => $dt]]],
            $rpc->getSerializable()
        );
    }

    public function testTreatmentsWithConfigByFlagSetRPC(): void
    {
        $dt = new \DateTime('now');
        $rpc = RPC::forTreatmentsWithConfigByFlagSet('key1', 'buck', 'set', ['a' => 'sarasa', 'b' => 2, 'c' => ['q', 'w'], 'd' => $dt]);
        $this->assertEquals(OpCode::TreatmentsWithConfigByFlagSet(), $rpc->getOpCode());
        $this->assertEquals(Version::V1(), $rpc->getVersion());
        $this->assertEquals('key1', $rpc->getArgs()[TreatmentsByFlagSetArgs::KEY()->getValue()]);
        $this->assertEquals('buck', $rpc->getArgs()[TreatmentsByFlagSetArgs::BUCKETING_KEY()->getValue()]);
        $this->assertEquals('set', $rpc->getArgs()[TreatmentsByFlagSetArgs::FLAG_SET()->getValue()]);
        $this->assertEquals('sarasa', $rpc->getArgs()[TreatmentsByFlagSetArgs::ATTRIBUTES()->getValue()]['a']);
        $this->assertEquals(2, $rpc->getArgs()[TreatmentsByFlagSetArgs::ATTRIBUTES()->getValue()]['b']);
        $this->assertEquals(['q', 'w'], $rpc->getArgs()[TreatmentsByFlagSetArgs::ATTRIBUTES()->getValue()]['c']);
        $this->assertEquals($dt, $rpc->getArgs()[TreatmentsByFlagSetArgs::ATTRIBUTES()->getValue()]['d']);
        $this->assertEquals(
            ['v' => 1, 'o' => 0x16, 'a' => ['key1', 'buck', 'set', ['a' => 'sarasa', 'b' => 2, 'c' => ['q', 'w'], 'd' => $dt]]],
            $rpc->getSerializable()
        );
    }

    public function testTreatmentsByFlagSetsRPC(): void
    {
        $dt = new \DateTime('now');
        $rpc = RPC::forTreatmentsByFlagSets('key1', 'buck', ['set_1', 'set_2'], ['a' => 'sarasa', 'b' => 2, 'c' => ['q', 'w'], 'd' => $dt]);
        $this->assertEquals(OpCode::TreatmentsByFlagSets(), $rpc->getOpCode());
        $this->assertEquals(Version::V1(), $rpc->getVersion());
        $this->assertEquals('key1', $rpc->getArgs()[TreatmentsByFlagSetsArgs::KEY()->getValue()]);
        $this->assertEquals('buck', $rpc->getArgs()[TreatmentsByFlagSetsArgs::BUCKETING_KEY()->getValue()]);
        $this->assertEquals(['set_1', 'set_2'], $rpc->getArgs()[TreatmentsByFlagSetsArgs::FLAG_SETS()->getValue()]);
        $this->assertEquals('sarasa', $rpc->getArgs()[TreatmentsByFlagSetsArgs::ATTRIBUTES()->getValue()]['a']);
        $this->assertEquals(2, $rpc->getArgs()[TreatmentsByFlagSetsArgs::ATTRIBUTES()->getValue()]['b']);
        $this->assertEquals(['q', 'w'], $rpc->getArgs()[TreatmentsByFlagSetsArgs::ATTRIBUTES()->getValue()]['c']);
        $this->assertEquals($dt, $rpc->getArgs()[TreatmentsByFlagSetsArgs::ATTRIBUTES()->getValue()]['d']);
        $this->assertEquals(
            ['v' => 1, 'o' => 0x17, 'a' => ['key1', 'buck', ['set_1', 'set_2'], ['a' => 'sarasa', 'b' => 2, 'c' => ['q', 'w'], 'd' => $dt]]],
            $rpc->getSerializable()
        );
    }

    public function testTreatmentsWithConfigByFlagSetsRPC(): void
    {
        $dt = new \DateTime('now');
        $rpc = RPC::forTreatmentsWithConfigByFlagSets('key1', 'buck', ['set_1', 'set_2'], ['a' => 'sarasa', 'b' => 2, 'c' => ['q', 'w'], 'd' => $dt]);
        $this->assertEquals(OpCode::TreatmentsWithConfigByFlagSets(), $rpc->getOpCode());
        $this->assertEquals(Version::V1(), $rpc->getVersion());
        $this->assertEquals('key1', $rpc->getArgs()[TreatmentsByFlagSetsArgs::KEY()->getValue()]);
        $this->assertEquals('buck', $rpc->getArgs()[TreatmentsByFlagSetsArgs::BUCKETING_KEY()->getValue()]);
        $this->assertEquals(['set_1', 'set_2'], $rpc->getArgs()[TreatmentsByFlagSetsArgs::FLAG_SETS()->getValue()]);
        $this->assertEquals('sarasa', $rpc->getArgs()[TreatmentsByFlagSetsArgs::ATTRIBUTES()->getValue()]['a']);
        $this->assertEquals(2, $rpc->getArgs()[TreatmentsByFlagSetsArgs::ATTRIBUTES()->getValue()]['b']);
        $this->assertEquals(['q', 'w'], $rpc->getArgs()[TreatmentsByFlagSetsArgs::ATTRIBUTES()->getValue()]['c']);
        $this->assertEquals($dt, $rpc->getArgs()[TreatmentsByFlagSetsArgs::ATTRIBUTES()->getValue()]['d']);
        $this->assertEquals(
            ['v' => 1, 'o' => 0x18, 'a' => ['key1', 'buck', ['set_1', 'set_2'], ['a' => 'sarasa', 'b' => 2, 'c' => ['q', 'w'], 'd' => $dt]]],
            $rpc->getSerializable()
        );
    }
}
