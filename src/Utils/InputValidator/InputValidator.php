<?php

namespace SplitIO\ThinSdk\Utils\InputValidator;

use SplitIO\ThinSdk\Utils\InputValidator\ValidationException;

use \Psr\Log\LoggerInterface;

const REG_EXP_FLAG_SET = "/^[a-z0-9][_a-z0-9]{0,49}$/";

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

    public function sanitize(string $flagSet, string $method): ?string
    {
        $trimmed = trim($flagSet);
        if ($trimmed !== $flagSet) {
            $this->logger->warning($method . ': Flag Set name ' . $flagSet . ' has extra whitespace, trimming');
        }
        $toLowercase = strtolower($trimmed);
        if ($toLowercase !== $trimmed) {
            $this->logger->warning($method . ': Flag Set name ' . $trimmed . ' should be all lowercase - converting string to lowercase');
        }
        if (!preg_match(REG_EXP_FLAG_SET, $toLowercase)) {
            $this->logger->warning($method . ': you passed ' . $toLowercase . ', Flag Set must adhere to the regular expressions {' .
                REG_EXP_FLAG_SET . ' This means a Flag Set must start with a letter or number, be in lowercase, alphanumeric and ' .
                'have a max length of 50 characters. ' . $toLowercase . ' was discarded.');
            return null;
        }

        return $toLowercase;
    }

    public function sanitizeMany(array $flagSets, string $method): ?array
    {
        if (!array_is_list($flagSets)) {
            $this->logger->error($method . ': FlagSets must be a non-empty list.');
            return null;
        }

        $sanitized = [];
        foreach ($flagSets as $flagSet) {
            $sanitizedFlagSet = $this->sanitize($flagSet, $method);
            if (!is_null($sanitizedFlagSet)) {
                array_push($sanitized, $sanitizedFlagSet);
            }
        }
        $sanitized = array_values(array_unique($sanitized));

        if (count($sanitized) == 0) {
            $this->logger->error($method . ': FlagSets must be a non-empty list.');
            return null;
        }

        return $sanitized;
    }
}

// TODO(mredolatti): remove when we deprecate php7
if (!function_exists('array_is_list')) {
    function array_is_list(array $a)
    {
        return $a === [] || array_keys($a) === range(0, count($a) - 1);
    }
}
