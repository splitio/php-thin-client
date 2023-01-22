<?php

namespace SplitIO\ThinClient\Link\Serialization;

use \SplitIO\ThinClient\Link\Protocol\RPC;

use MessagePack\Packer;
use MessagePack\Type\Map;
use MessagePack\BufferUnpacker;

class MessagePack implements Serializer
{
	private Packer $packer;

	public function __construct()
	{
		$this->packer = new Packer();
	}

	public function serialize(RPC $rpc)
	{
		return $this->packer->pack(new Map($rpc->toArray()));
	}

	public function deserialize(string $raw)
	{
		$unpacker = new BufferUnpacker($raw, null);
		return $unpacker->unpack();
	}
}
