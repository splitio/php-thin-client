<?php

namespace SplitIO\ThinClient\Link\Transfer\Framing;

class LengthPrefix implements Framer
{
    public function Frame(string $message): string
    {
        $prefix = pack("V", strlen($message));
        return $prefix . $message;
    }

    public function ReadFrame(/*\Socket */$sock, string &$buffer): int
    {
        $sizeBuffer = "    ";
        $n = socket_recv($sock, $sizeBuffer, 4, 0);
        if ($n != 4) {
            throw new \Exception("wrong number of bytes from size");
        }

        $size = unpack("V", $sizeBuffer);
        if (!isset($size[1])) {
            throw new \Exception("size parsing failed");
        }
        $size = $size[1];

        $n = socket_recv($sock, $buffer, $size, 0);
        if ($size != $n) {
            throw new \Exception("size mismatch. got: $n -- expected: $size");
        }

        return $n;
    }
}
