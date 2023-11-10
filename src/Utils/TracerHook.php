<?php

namespace SplitIO\ThinSdk\Utils;

interface TracerHook
{
    function on(int $method, int $event, ?array $args);
}
