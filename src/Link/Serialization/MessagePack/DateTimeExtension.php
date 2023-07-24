<?php

namespace SplitIO\ThinSdk\Link\Serialization\MessagePack;

use MessagePack\CanPack;
use MessagePack\Packer;
use MessagePack\Type\Timestamp;

class DateTimeExtension implements CanPack
{

    public function pack(Packer $packer, $value): ?string
    {
        return ($value instanceof \DateTimeInterface)
            ? $packer->pack(Timestamp::fromDateTime($value))
            : null;
    }
}
