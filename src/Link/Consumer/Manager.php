<?php

namespace SplitIO\ThinClient\Link\Consumer;

interface Manager
{
    function getTreatment(string $key, ?string $bucketingKey, string $feature, ?array $attributes): array;
    function getTreatments(string $key, ?string $bucketingKey, array $features, ?array $attributes): array;
}

