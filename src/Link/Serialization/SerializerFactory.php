<?php

namespace SplitIO\ThinClient\Link\Serialization;

class SerializerFactory
{

    private $mechanism;

    public function __construct(\SplitIO\ThinClient\Config\Serialization $options)
    {
        $this->mechanism = $options->mechanism();
    }

    public function create(): Serializer
    {
        switch ($this->mechanism) {
            case 'msgpack':
                return new MessagePack();
        }

        throw new \Exception("invalid serialization mechanism specified");
    }

 }
