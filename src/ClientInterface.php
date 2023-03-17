<?php

namespace SplitIO\ThinClient;

interface ClientInterface
{
	public function getTreatment(string $key, ?string $bucketingKey, string $feature, ?array $attributes): string;
}
