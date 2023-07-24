<?php

namespace SplitIO\ThinSdk\Link\Serialization\MessagePack;

use MessagePack\CanPack;
use MessagePack\Packer;
use MessagePack\Type\Timestamp;

class DateTimeExtension implements CanPack
{

    public function pack(Packer $packer, $value) : ?string
    {
        if (!$value instanceof \DateTimeInterface) {
            return null;
        }

        return $packer->pack(Timestamp::fromDateTime($value));
    }
}
