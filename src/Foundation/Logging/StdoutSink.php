<?php

namespace SplitIO\ThinClient\Foundation\Logging;

class StdoutSink implements Sink
{
    public function write(\Stringable|string $message): void
    {
        fwrite(STDOUT, $message . PHP_EOL);
    }
}
