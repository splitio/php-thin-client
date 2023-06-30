<?php

namespace SplitIO\ThinSdk\Link\Transfer;

interface RawConnection
{
    public function sendMessage(string $message);
    public function readMessage(): string;
    public function isHealthy(): bool;
}
