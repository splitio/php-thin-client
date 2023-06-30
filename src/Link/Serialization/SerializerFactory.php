<?php

namespace SplitIO\ThinSdk\Link\Serialization;

class SerializerFactory
{

    private $mechanism;

    public function __construct(\SplitIO\ThinSdk\Config\Serialization $options)
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
