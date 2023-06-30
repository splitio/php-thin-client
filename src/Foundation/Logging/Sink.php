<?php

namespace SplitIO\ThinSdk\Foundation\Logging;

interface Sink
{
    function write(/*\Stringable|string*/ $message): void;
}
