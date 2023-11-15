<?php

namespace SplitIO\ThinSdk\Utils;

class NoOpTracerHook implements TracerHook
{
    public function on(int $method, int $event, ?array $args)
    {
    }
}
