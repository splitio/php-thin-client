<?php

namespace SplitIO\ThinSdk\Utils\InputValidator;

use SplitIO\ThinSdk\Utils\InputValidator\ValidationException;

use \Psr\Log\LoggerInterface;

class InputValidator
{

    private /*LoggerInterface*/ $logger;


    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function validProperties(?array $properties): ?array
    {
        if (is_null($properties)) {
            return null;
        }

        if (array_is_list($properties)) {
            throw new ValidationException('track: properties must be of type associative array.');
        }

        $propBuilder = new EventPropertiesBuilder($this->logger);
        foreach ($properties as $key => $value) {
            $propBuilder->add($key, $value);
        }

        $validProperties = $propBuilder->get();
        if (count($validProperties) > 300) {
            $this->logger->warning('Event has more than 300 properties. Some of them will be trimmed when processed');
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
