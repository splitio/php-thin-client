<?php

namespace SplitIO\ThinSdk\Link\Consumer;

interface Manager
{
    function getTreatment(string $key, ?string $bucketingKey, string $feature, ?array $attributes): array;
    function getTreatments(string $key, ?string $bucketingKey, array $features, ?array $attributes): array;
    function track(string $key, string $trafficType, string $eventType, ?float $value, ?array $properties): bool;
}

