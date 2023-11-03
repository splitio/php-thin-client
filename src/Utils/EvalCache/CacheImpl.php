<?php

namespace SplitIO\ThinSdk\Utils\EvalCache;

class CacheImpl implements Cache
{

    private /*array*/ $data;
    private /*InputHasher*/ $hasher;

    public function __construct(InputHasher $hasher)
    {
        $this->data = [];
        $this->hasher = $hasher;
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

    public function set(string $key, string $feature, ?array $attributes, string $treatment)
    {
        $h = $this->hasher->hashInput($key, $feature, $attributes);
        $this->data[$h] = new Entry($treatment, false);
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
    }

    public function setManyWithConfig(string $key, ?array $attributes, array $results)
    {
        foreach ($results as $feature => $result) {
            $this->setWithConfig($key, $feature, $attributes, $result['treatment'], $result['config']);
        }
    }

    private function _get(string $key, string $feature, ?array $attributes): ?Entry
    {
        $h = $this->hasher->hashInput($key, $feature, $attributes);
        return $this->data[$h] ?? null;
    }
}
