<?php

namespace SplitIO\ThinClient\Link\Transfer\Framing;

interface Framer
{
    function Frame(string $message): string;
    function ReadFrame(\Socket $sock, string &$buffer): int;
}
