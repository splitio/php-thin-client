<?php

namespace SplitIO\ThinClient\Foundation\Logging;

interface Sink
{
    function write(\Stringable|string $message): void;
}
