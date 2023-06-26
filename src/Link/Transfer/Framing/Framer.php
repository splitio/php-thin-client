<?php

namespace SplitIO\ThinClient\Link\Transfer\Framing;

interface Framer
{
    function SendFrame(/*\Socket */ $sock, string $message): int;
    function ReadFrame(/*\Socket */ $sock, string &$buffer): int;
}
