<?php

namespace SplitIO\ThinClient;

use \Psr\Log\LoggerInterface;

class Client implements ClientInterface
{
    private Link\Manager $lm;
    private LoggerInterface $logger;

    public function __construct(Link\Manager $manager, LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->lm = $manager;
    }

    public function getTreatment(string $key, ?string $bucketingKey, string $feature, ?array $attributes): string
    {
        try {
            return $this->lm->getTreatment($key, $bucketingKey, $feature, $attributes)['Treatment'];
        } catch (\Exception $exc) {
            $this->logger->error($exc);
            return "control";
        }
    }
}
