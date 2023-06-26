<?php

namespace SplitIO\ThinClient\Link\Serialization;

use \SplitIO\ThinClient\Link\Serialization\Serializable;

interface Serializer
{
    public function serialize(Serializable $item);
    public function deserialize(string $raw)/*: mixed*/;
}
