<?php

namespace SplitIO\ThinClient\Link\Serialization;

interface Deserializable
{
    static function fromRaw(mixed $raw): mixed;
}
