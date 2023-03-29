<?php

namespace SplitIO\ThinClient\Link\Serialization;

class Initializer
{
    public static function setup(\SplitIO\ThinClient\Config\Serialization $options): Serializer
    {
        switch ($options->mechanism()) {
            case 'msgpack':
                return new MessagePack();
        }

        throw new \Exception("invalid serialization mechanism specified");
    }
}
