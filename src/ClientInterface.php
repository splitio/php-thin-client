<?php

namespace SplitIO\ThinSdk;

interface ClientInterface
{
    function getTreatment(string $key, ?string $bucketingKey, string $feature, ?array $attributes): string;
    function getTreatmentWithConfig(string $key, ?string $bucketingKey, string $feature, ?array $attributes): array;
    function getTreatments(string $key, ?string $bucketingKey, array $features, ?array $attributes): array;
    function getTreatmentsWithConfig(string $key, ?string $bucketingKey, array $features, ?array $attributes): array;
    function track(string $key, string $trafficType, string $eventType, ?float $value = null, ?array $properties = null): bool;
}
