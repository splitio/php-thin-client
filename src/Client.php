<?php

namespace SplitIO\ThinClient;


class Client implements ClientInterface
{
	private Link\Manager $lm;

	public function __construct(Link\Manager $manager)
	{
		$this->lm = $manager;
	}

	public function getTreatment(string $key, ?string $bucketingKey, string $feature, ?array $attributes): string
	{
		try {
			return $this->lm->getTreatment($key, $bucketingKey, $feature, $attributes)['Treatment'];
		} catch (\Exception $exc) {
			// TODO(mredolatti): log properly!
			printf("%s\n", $exc);
			return "control";
		}
	}
}
