<?php

namespace SplitIO\ThinSdk\Utils\Tracing;

interface TracerHook
{
    function on(array $event);
}
