<?php

namespace SplitIO\ThinSdk\Link\Serialization;

use \SplitIO\ThinSdk\Link\Serialization\Serializable;

interface Serializer
{
    public function serialize(Serializable $item);
    public function deserialize(string $raw)/*: mixed*/;
}
