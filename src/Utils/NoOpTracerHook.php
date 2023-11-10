<?php

namespace SplitIO\ThinSdk\Utils;

class NoOpTracerHook implements TracerHook
{
    function on(int $method, int $event, ?array $args)
    {
    }
}
