<?php

namespace SplitIO\ThinSdk\Utils\EvalCache;

class NoCache implements Cache
{
    public function get(string $key, string $feature, ?array $attributes): ?string
    {
        return null;
    }

    public function getMany(string $key, array $features, ?array $attributes): array
    {
        $res = [];
        foreach ($features as $feature) {
            $res[$feature] = null;
        }
        return $res;
    }

    public function getWithConfig(string $key, string $feature, ?array $attributes): ?array
    {
        return null;
    }

    public function getManyWithConfig(string $key, array $features, ?array $attributes): array
    {
        $res = [];
        foreach ($features as $feature) {
            $res[$feature] = null;
        }
        return $res;
    }

    public function getByFlagSets(array $flagSets, string $key, ?array $attributes, bool $withConfig): ?array
    {
        return null;
    }

    public function set(string $key, string $feature, ?array $attributes, string $treatment)
    {
    }

    public function setMany(string $key, ?array $attributes, array $results)
    {
    }

    public function setWithConfig(string $key, string $feature, ?array $attributes, string $treatment, ?string $config)
    {
    }

    public function setManyWithConfig(string $key, ?array $attributes, array $results)
    {
    }

    public function setFeaturesForFlagSets(string $key, array $flagSets, ?array $attributes, array $results, bool $withConfig)
    {
    }
}
