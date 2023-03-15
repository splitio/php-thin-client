<?php

namespace SplitIO\ThinClient\Link\Serialization;

use \SplitIO\ThinClient\Link\Protocol\RPC;

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

	public function serialize(RPC $rpc)
	{
		return $this->packer->pack(new Map($rpc->toArray()));
	}

	public function deserialize(string $raw)
	{
		$this->unpacker->reset($raw);
		return $this->unpacker->unpack();
	}
}
