<?php

namespace SplitIO\Test\Fallback;

use SplitIO\ThinSdk\Fallback\GenericFallbackFactory;
use SplitIO\ThinSdk\ClientInterface;
use SplitIO\ThinSdk\ManagerInterface;

use PHPUnit\Framework\TestCase;

class AlwaysEmptyManagerTest extends TestCase
{

    public function testSplitNames()
    {
        $client = $this->createMock(ClientInterface::class);
        $manager = $this->createMock(ManagerInterface::class);
        $factory = new GenericFallbackFactory($client, $manager);

        $this->assertEquals($client, $factory->client());
        $this->assertEquals($manager, $factory->manager());
    }
}
