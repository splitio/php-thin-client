<?php

namespace SplitIO\ThinSdk\Link\Consumer;

use \SplitIO\ThinSdk\SplitView;

interface Manager
{
    function getTreatment(string $key, ?string $bucketingKey, string $feature, ?array $attributes): array;
    function getTreatmentWithConfig(string $key, ?string $bucketingKey, string $feature, ?array $attributes): array;
    function getTreatments(string $key, ?string $bucketingKey, array $features, ?array $attributes): array;
    function getTreatmentsWithConfig(string $key, ?string $bucketingKey, array $features, ?array $attributes): array;
    function getTreatmentsByFlagSet(string $key, ?string $bucketingKey, string $flagSet, ?array $attributes): array;
    function getTreatmentsWithConfigByFlagSet(
        string $key,
        ?string $bucketingKey,
        string $flagSet,
        ?array $attributes
    ): array;
    function getTreatmentsByFlagSets(string $key, ?string $bucketingKey, array $flagSets, ?array $attributes): array;
    function getTreatmentsWithConfigByFlagSets(
        string $key,
        ?string $bucketingKey,
        array $flagSet,
        ?array $attributes
    ): array;
    function track(string $key, string $trafficType, string $eventType, ?float $value, ?array $properties): bool;
    function splitNames(): array;
    function split(string $splitName): ?SplitView;
    function splits(): array;
}
