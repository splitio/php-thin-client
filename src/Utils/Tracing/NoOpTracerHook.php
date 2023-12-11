<?php

namespace SplitIO\ThinSdk\Utils\Tracing;

class NoOpTracerHook implements TracerHook
{
    function on(array $event)
    {
    }
}
