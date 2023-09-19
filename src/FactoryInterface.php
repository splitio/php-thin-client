<?php

namespace SplitIO\ThinSdk;

interface FactoryInterface
{
    public function client(): ClientInterface;
    public function manager(): ManagerInterface;
};
