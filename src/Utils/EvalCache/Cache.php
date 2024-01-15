<?php

namespace SplitIO\ThinSdk\Utils\EvalCache;

interface Cache
{
    public function get(string $key, string $feature, ?array $attributes): ?string;
    public function getMany(string $key, array $features, ?array $attributes): array;
    public function getWithConfig(string $key, string $feature, ?array $attributes): ?array;
    public function getManyWithConfig(string $key, array $features, ?array $attributes): array;
    public function getFeaturesByFlagSets(array $flagSets): ?array;
    public function set(string $key, string $feature, ?array $attributes, string $treatment);
    public function setMany(string $key, ?array $attributes, array $results);
    public function setWithConfig(string $key, string $feature, ?array $attributes, string $treatment, ?string $config);
    public function setManyWithConfig(string $key, ?array $attributes, array $results);
    public function setFeaturesForFlagSets(array $flagSets, array $featuresFlags);
}
