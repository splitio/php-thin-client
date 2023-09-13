<?php

namespace SplitIO\ThinSdk\Fallback;

use SplitIO\ThinSdk\ClientInterface;

class AlwaysControlClient implements ClientInterface
{
    public function getTreatment(string $key, ?string $bucketingKey, string $feature, ?array $attributes): string
    {
        return "control";
    }

    public  function getTreatments(string $key, ?string $bucketingKey, array $features, ?array $attributes): array
    {
        return array_reduce($features, function ($carry, $item) {
            $carry[$item] = "control";
            return $carry;
        }, []);
    }
    public function track(string $key, string $trafficType, string $eventType, ?float $value = null, ?array $properties = null): bool
    {
        return false;
    }
}