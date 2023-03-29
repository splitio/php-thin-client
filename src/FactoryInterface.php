<?php

namespace SplitIO\ThinClient;

interface FactoryInterface
{
    public function client(): ClientInterface;
};
