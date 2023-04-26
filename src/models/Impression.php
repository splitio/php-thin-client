<?php

namespace SplitIO\ThinClient\Models;

class Impression
{
    private string $key;
    private ?string $bucketingKey;
    private string $feature;
    private string $treatment;
    private string $label;
    private int $changeNumber;
    private int $timestamp;

    public function __construct(string $key,
        ?string $bucketingKey,
        string $feature,
        string $treatment,
        string $label,
        int $changeNumber,
        int $timestamp)
    {
        $this->key = $key;
        $this->bucketingKey = $bucketingKey;
        $this->feature = $feature;
        $this->treatment = $treatment;
        $this->label = $label;
        $this->changeNumber = $changeNumber;
        $this->timestamp = $timestamp;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getBucketingKey(): ?string
    {
        return $this->bucketingKey;
    }

    public function getFeature(): string
    {
        return $this->feature;
    }

    public function getTreatment(): string
    {
        return $this->treatment;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getChangeNumber(): int
    {
        return $this->changeNumber;
    }

    public function getTimestamp(): int
    {
        return $this->timestamp;
    }
};
