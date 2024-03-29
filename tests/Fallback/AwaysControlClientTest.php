<?php

namespace SplitIO\Test\Fallback;

use SplitIO\ThinSdk\Fallback\AlwaysControlClient;

use PHPUnit\Framework\TestCase;

class AlwaysControlClientTest extends TestCase
{

    public function testGetTreatment()
    {
        $c = new AlwaysControlClient();
        $this->assertEquals("control", $c->getTreatment("key", null, "feature", null));
        $this->assertEquals("control", $c->getTreatment("key", "buck", "feature", ['a' => 1]));
        $this->assertEquals("control", $c->getTreatment("", null, "", null));
    }

    public function testGetTreatments()
    {
        $c = new AlwaysControlClient();
        $this->assertEquals(
            ["f1" => "control", "f2" => "control"],
            $c->getTreatments("key", null, ["f1", "f2"], null)
        );
        $this->assertEquals([], $c->getTreatments("key", null, [], null));
    }

    public function testGetTreatmentsByFlagSet()
    {
        $c = new AlwaysControlClient();
        $this->assertEquals([], $c->getTreatmentsByFlagSet("key", null, "someFlagSet", null));
        $this->assertEquals([], $c->getTreatmentsWithConfigByFlagSet("key", null, "someFlagSet", null));
    }

    public function testGetTreatmentsByFlagSets()
    {
        $c = new AlwaysControlClient();
        $this->assertEquals([], $c->getTreatmentsByFlagSets("key", null, ['set_1', 'set_2'], null));
        $this->assertEquals([], $c->getTreatmentsWithConfigByFlagSets("key", null, ['set_1', 'set_2'], null));
    }

    public function testTrack()
    {
        $c = new AlwaysControlClient();
        $this->assertEquals(false, $c->track("k", "tt", "et", null, null));
        $this->assertEquals(false, $c->track("k", "tt", "et", 1.23, null));
        $this->assertEquals(false, $c->track("k", "tt", "et", null, []));
        $this->assertEquals(false, $c->track("k", "tt", "et", null, ['a' => 1]));
    }
}
