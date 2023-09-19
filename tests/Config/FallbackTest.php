<?php

namespace SplitIO\Test\Link\Consumer;

use SplitIO\ThinSdk\Config\Fallback;
use SplitIO\ThinSdk\Fallback\AlwaysControlClient;
use SplitIO\ThinSdk\Fallback\AlwaysEmptyManager;
use SplitIO\ThinSdk\ClientInterface;
use SplitIO\ThinSdk\ManagerInterface;

use PHPUnit\Framework\TestCase;

class FallbackTest extends TestCase
{

    public function testConfigDefault()
    {
        $cfg = Fallback::default();
        $this->assertEquals(false, $cfg->disable());
        $this->assertEquals(new AlwaysControlClient(), $cfg->client());
        $this->assertEquals(new AlwaysEmptyManager(), $cfg->manager());
    }

    public function testConfigParsing()
    {
        $clientMock = $this->createMock(ClientInterface::class);
        $managerMock = $this->createMock(ManagerInterface::class);
        $cfg = Fallback::fromArray([
            'disable' => true,
            'client' => $clientMock,
            'manager' => $managerMock,
        ]);

        $this->assertEquals(true, $cfg->disable());
        $this->assertEquals($clientMock, $cfg->client());
        $this->assertEquals($managerMock, $cfg->manager());
    }
}
