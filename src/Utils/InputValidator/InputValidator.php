<?php

namespace SplitIO\ThinSdk\Utils\InputValidator;

use SplitIO\ThinSdk\Utils\InputValidator\ValidationException;

use \Psr\Log\LoggerInterface;

class InputValidator
{

    private /*LoggerInterface*/ $logger;

    const MAX_PROPERTIES_LENGTH_BYTES = 32768;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function validProperties(?array $properties): ?array
    {
        if (is_null($properties)) {
            return null;
        }

        if (!array_is_list($properties)) {
            throw new ValidationException('track: properties must be of type associative array.');
        }

        $size = 1024; // We assume 1kb events without properties (750 bytes avg measured)

        $validProperties = array();
        foreach ($properties as $property => $element) {
            // Exclude property if is not string
            if (!is_string($property)) {
                continue;
            }

            $validProperties[$property] = null;
            $size += strlen($property);

            if (is_null($element)) {
                continue;
            }

            if (!is_string($element) && !is_bool($element) && !is_int($element) && !is_float($element)) {
                $this->logger->warning('Property ' . json_encode($element) . ' is of invalid type.'
                    . ' Setting value to null');
                $element = null;
            }

            $validProperties[$property] = $element;

            if (is_string($element)) {
                $size += strlen($element);
            }

            if ($size > self::MAX_PROPERTIES_LENGTH_BYTES) {
                throw new ValidationException("The maximum size allowed for the properties is 32768 bytes. "
                    . "Current one is " . strval($size) . " bytes. Event not queued");
            }
        }

        if (is_array($validProperties) && count($validProperties) > 300) {
            $this->logger->warning('Event has more than 300 properties. Some of them will be '
                . 'trimmed when processed');
        }

        return count($validProperties) > 0 ? $validProperties : null;
    }
}

// TODO(mredolatti): remove when we deprecate php7
if (!function_exists('array_is_list')) {
    function array_is_list(array $a)
    {
        return $a === [] || array_keys($a) === range(0, count($a) - 1);
    }
}
