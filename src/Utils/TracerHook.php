<?php

namespace SplitIO\ThinSdk\Utils;

interface TracerHook
{
    public function on(int $method, int $event, ?array $args);
}
