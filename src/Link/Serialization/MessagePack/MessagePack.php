<?php

namespace SplitIO\ThinSdk\Link\Serialization\MessagePack;

use SplitIO\ThinSdk\Link\Serialization\Serializer;
use SplitIO\ThinSdk\Link\Serialization\Serializable;

use MessagePack\Packer;
use MessagePack\BufferUnpacker;
use MessagePack\Extension\TimestampExtension;

class MessagePack implements Serializer
{
    private /*Packer*/ $packer;
    private /*BufferUnpacker*/ $unpacker;

    public function __construct()
    {
        $this->packer = (new Packer())->extendWith(new TimestampExtension(), new DateTimeExtension());
        $this->unpacker = (new BufferUnpacker())->extendWith(new TimestampExtension());
    }

    public function serialize(Serializable $item)
    {
        return $this->packer->pack($item->getSerializable());
    }

    public function deserialize(string $raw)/*: mixed*/
    {
        $this->unpacker->reset($raw);
        return $this->unpacker->unpack();
    }
}
