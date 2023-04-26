<?php

namespace SplitIO\ThinClient\Link\Serialization;


use MessagePack\Packer;
use MessagePack\Type\Map;
use MessagePack\BufferUnpacker;
use MessagePack\Extension\TimestampExtension;

class MessagePack implements Serializer
{
    private Packer $packer;
    private BufferUnpacker $unpacker;

    public function __construct()
    {
        $this->packer = (new Packer())->extendWith(new TimestampExtension());
        $this->unpacker = (new BufferUnpacker())->extendWith(new TimestampExtension());
    }

    public function serialize(Serializable $item, bool $emptyArrayAsMap)
    {
        return $this->packer->pack(
            $emptyArrayAsMap ? new Map($item->getSerializable()) : $item->getSerializable()
        );
    }

    public function deserialize(string $raw): mixed
    {
        $this->unpacker->reset($raw);
        return $this->unpacker->unpack();
    }
}
