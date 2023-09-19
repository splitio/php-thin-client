<?php

namespace SplitIO\Test\Fallback;

use SplitIO\ThinSdk\Fallback\AlwaysEmptyManager;

use PHPUnit\Framework\TestCase;

class AlwaysEmptyManagerTest extends TestCase
{

    public function testSplitNames()
    {
        $m = new AlwaysEmptyManager();
        $this->assertEquals([], $m->splitNames());
    }

    public function testGetTreatments()
    {
        $m = new AlwaysEmptyManager();
        $this->assertEquals(null, $m->split('some'));
        $this->assertEquals(null, $m->split(''));
    }

    public function testTrack()
    {
        $m = new AlwaysEmptyManager();
        $this->assertEquals([], $m->splits());
    }
}
