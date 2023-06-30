<?php

namespace SplitIO\ThinSdk;

interface ClientInterface
{
    public function getTreatment(string $key, ?string $bucketingKey, string $feature, ?array $attributes): string;
    public function getTreatments(string $key, ?string $bucketingKey, array $features, ?array $attributes): array;
}
