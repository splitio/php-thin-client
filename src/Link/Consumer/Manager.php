<?php

namespace SplitIO\ThinClient\Link\Consumer;

use \SplitIO\ThinClient\Link\Protocol\V1\TreatmentResponse;

interface Manager
{
    function getTreatment(string $key, ?string $bucketingKey, string $feature, ?array $attributes): TreatmentResponse;
    function getTreatments(string $key, ?string $bucketingKey, array $features, ?array $attributes): array;
}

