<?php

namespace SplitIO\ThinSdk\Link\Serialization;

interface Deserializable
{
    static function fromRaw(/*mixed*/ $raw)/*: mixed*/;
}
