<?php

namespace SplitIO\ThinSdk\Utils\InputValidator;

use \Psr\Log\LoggerInterface;


class EventPropertiesBuilder
{
    private $props;
    private $size;
    private $logger;

    const MAX_PROPERTIES_LENGTH_BYTES = 32768;

    public function __construct(LoggerInterface $logger)
    {
        $this->props = [];
        $this->size = 1024;
        $this->logger = $logger;
    }

    public function add($key, $value)
    {
        if (!is_string($key)) {
            return;
        }

        $this->props[$key] = null;
        $this->size += strlen($key);

        if (is_null($value)) {
            return;
        }

        if (!$this->isSupportedType($value)) {
            $this->logger->warning('Property ' . json_encode($value) . ' is of invalid type. Setting value to null');
            $value = null;
        }

        $this->props[$key] = $value;

        if (is_string($value)) {
            $this->size += strlen($value);
        }

        if ($this->size > self::MAX_PROPERTIES_LENGTH_BYTES) {
            throw new ValidationException("The maximum size allowed for the properties is 32768 bytes. "
                . "Current one is " . strval($this->size) . " bytes. Event not queued");
        }
    }

    public function get()
    {
        return $this->props;
    }

    private function isSupportedType($value)
    {
        return is_string($value) || is_bool($value) || is_int($value) || !is_float($value);
    }
}
