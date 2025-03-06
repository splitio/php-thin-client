<?php

namespace SplitIO\ThinSdk\Link\Transfer\Framing;

use SplitIO\ThinSdk\Link\Transfer\Helpers;

class LengthPrefix implements Framer
{

    private const HEADER_SIZE_BYTES = 4;
    private const UNPACK_TYPE = 'V';

    public function SendFrame(/*Socket*/$socket, string $message): int
    {
        $toSend = pack(self::UNPACK_TYPE, strlen($message)) . $message;

        $sent = 0;
        while ($sent < strlen($toSend)) {
            $sentBytes = Helpers::wrapSocketOperation('socket_send', $socket, $toSend, strlen($toSend), 0);
            $toSend = substr($toSend, $sentBytes);
            $sent += $sentBytes;
        }

        return $sent - self::HEADER_SIZE_BYTES;
    }

    public function ReadFrame(/*\Socket */$sock, string &$buffer): int
    {
        $sizeBuffer = '';
        $sizeByteCount = Helpers::wrapSocketOperation('socket_recv', $sock, $sizeBuffer, self::HEADER_SIZE_BYTES, 0);
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
            $read += Helpers::wrapSocketOperation('socket_recv', $sock, $buf, $size, 0);
            $buffer .= $buf;
        }

        return  $read - self::HEADER_SIZE_BYTES;
    }
}
