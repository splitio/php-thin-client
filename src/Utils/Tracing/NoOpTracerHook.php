<?php

namespace SplitIO\ThinSdk\Utils\Tracing;

class NoOpTracerHook implements TracerHook
{
    public function on(array $event)
    {
    }
}
