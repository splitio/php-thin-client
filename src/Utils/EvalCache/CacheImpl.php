<?php

namespace SplitIO\ThinSdk\Utils\EvalCache;

class CacheImpl implements Cache
{

    private /*array*/ $data;
    private /*InputHasher*/ $hasher;
    private /*EvictionPolicy*/ $evictionPolicy;
    private /*array*/ $flagSets;

    public function __construct(InputHasher $hasher, EvictionPolicy $evictionPolicy)
    {
        $this->data = [];
        $this->hasher = $hasher;
        $this->evictionPolicy = $evictionPolicy;
    }

    public function get(string $key, string $feature, ?array $attributes): ?string
    {
        $entry = $this->_get($key, $feature, $attributes);
        return ($entry != null) ? $entry->getTreatment() : null;
    }

    public function getMany(string $key, array $features, ?array $attributes): array
    {
        $result = [];
        foreach ($features as $feature) {
            $result[$feature] = $this->get($key, $feature, $attributes);
        }
        return $result;
    }

    public function getWithConfig(string $key, string $feature, ?array $attributes): ?array
    {
        // if the entry exists but was previously fetched without config, it's returned as null,
        // so that it's properly fetched by `getTreatmentWithConfig`
        $entry = $this->_get($key, $feature, $attributes);
        return ($entry != null && $entry->hasConfig())
            ? ['treatment' => $entry->getTreatment(), 'config' => $entry->getConfig()]
            : null;
    }

    public function getManyWithConfig(string $key, array $features, ?array $attributes): array
    {
        $result = [];
        foreach ($features as $feature) {
            $result[$feature] = $this->getWithConfig($key, $feature, $attributes);
        }
        return $result;
    }

    public function getByFlagSets(array $flagSets, string $key, ?array $attributes, bool $withConfig): array
    {
        sort($flagSets);
        $h = implode(",", $flagSets);
        $features = $this->flagSets[$h] ?? null;
        if (is_null($features)) {
            return [];
        }
        return $withConfig ? $this->getManyWithConfig($key, $$features, $attributes) : $this->getMany($key, $$features, $attributes);
    }

    public function set(string $key, string $feature, ?array $attributes, string $treatment)
    {
        $h = $this->hasher->hashInput($key, $feature, $attributes);
        $this->data[$h] = new Entry($treatment, false);
        $this->evictionPolicy->postCacheInsertionHook($h, $this->data);
    }

    public function setMany(string $key, ?array $attributes, array $results)
    {
        foreach ($results as $feature => $treatment) {
            $this->set($key, $feature, $attributes, $treatment);
        }
    }

    public function setWithConfig(string $key, string $feature, ?array $attributes, string $treatment, ?string $config)
    {
        $h = $this->hasher->hashInput($key, $feature, $attributes);
        $this->data[$h] = new Entry($treatment, true, $config);
        $this->evictionPolicy->postCacheInsertionHook($h, $this->data);
    }

    public function setManyWithConfig(string $key, ?array $attributes, array $results)
    {
        foreach ($results as $feature => $result) {
            $this->setWithConfig($key, $feature, $attributes, $result['treatment'], $result['config']);
        }
    }

    public function setFeaturesForFlagSets(string $key, array $flagSets, ?array $attributes, array $results, bool $withConfig)
    {
        $h = implode(",", $flagSets);
        $this->flagSets[$h] = array_keys($results);
        if ($withConfig) {
            $this->setManyWithConfig($key, $attributes, $results);
            return;
        }
        $this->setMany($key, $attributes, $results);
    }

    private function _get(string $key, string $feature, ?array $attributes): ?Entry
    {
        $h = $this->hasher->hashInput($key, $feature, $attributes);
        return $this->data[$h] ?? null;
    }
}
