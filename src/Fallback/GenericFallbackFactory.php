<?php

namespace SplitIO\ThinSdk\Fallback;

use SplitIO\ThinSdk\FactoryInterface;
use SplitIO\ThinSdk\ClientInterface;
use SplitIO\ThinSdk\ManagerInterface;

class GenericFallbackFactory implements FactoryInterface
{

    private /*ClientInterface*/ $client;
    private /*ManagerInterface*/ $manager;

    public function __construct(ClientInterface $client, ManagerInterface $manager)
    {
        $this->client = $client;
        $this->manager = $manager;
    }
    public function client(): ClientInterface
    {
        return $this->client;
    }

    public function manager(): ManagerInterface
    {
        return $this->manager;
    }
}
