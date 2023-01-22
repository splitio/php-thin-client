<?php

namespace SplitIO\ThinClient\Link\Serialization;

use \SplitIO\ThinClient\Link\Protocol\RPC;

interface Serializer
{
	public function serialize(RPC $rpc);
	public function deserialize(string $raw);
}
