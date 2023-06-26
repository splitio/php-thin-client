<?php

namespace SplitIO\ThinClient\Link\Transfer\Framing;

use SplitIO\ThinClient\Link\Transfer\StreamSocketHelpers;

class LengthPrefix implements Framer
{

    private const HEADER_SIZE_BYTES = 4;
    private const UNPACK_TYPE = 'V';

    public function SendFrame(/*Socket*/ $socket, string $message): int
    {
        $toSend = pack(self::UNPACK_TYPE, strlen($message)) . $message;

        $sent = 0;
        while ($sent < strlen($toSend)) {
            $sent += StreamSocketHelpers::writeOrThrow($socket, substr($toSend, $sent));
        }

        return $sent - self::HEADER_SIZE_BYTES;
    }

    public function ReadFrame(/*\Socket */$sock, string &$buffer): int
    {
        $sizeBuffer = '';
        $sizeByteCount = StreamSocketHelpers::readOrThrow($sock, $sizeBuffer, self::HEADER_SIZE_BYTES);
        if ($sizeByteCount != self::HEADER_SIZE_BYTES) {
            throw new FramingException("invalid header size: $sizeByteCount");
        }

        $size = unpack("V", $sizeBuffer);
        if (!isset($size[1])) {
            throw new FramingException("message size unpacking failed");
        }

        $size = $size[1];
        $read = 0;
        while ($read < $size) {
            $buf = '';
            $read += StreamSocketHelpers::readOrThrow($sock, $buf, $size);
            $buffer .= $buf;
        }

        return  - self::HEADER_SIZE_BYTES;
    }
}
