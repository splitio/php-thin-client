<?php

namespace SplitIO\ThinClient\Link\Serialization;


use MessagePack\Packer;
use MessagePack\BufferUnpacker;
use MessagePack\Extension\TimestampExtension;

class MessagePack implements Serializer
{
    private /*Packer*/ $packer;
    private /*BufferUnpacker*/ $unpacker;

    public function __construct()
    {
        $this->packer = (new Packer())->extendWith(new TimestampExtension());
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
