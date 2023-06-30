<?php

namespace SplitIO\ThinClient;

use \SplitIO\ThinClient\Utils\ImpressionListener;
use \SplitIO\ThinClient\Models\Impression;
use \SplitIO\ThinClient\Link\Consumer\Manager;
use \SplitIO\ThinClient\Link\Protocol\V1\ImpressionListenerData;
use \Psr\Log\LoggerInterface;


class Client implements ClientInterface
{
    private /*Link\Consumer\Manager*/ $lm;
    private /*LoggerInterface*/ $logger;
    private /*?ImpressionListener*/ $impressionListener;

    public function __construct(Manager $manager, LoggerInterface $logger, ?ImpressionListener $impressionListener)
    {
        $this->logger = $logger;
        $this->lm = $manager;
        $this->impressionListener = $impressionListener;
    }

    public function getTreatment(string $key, ?string $bucketingKey, string $feature, ?array $attributes): string
    {
        try {
            list($treatment, $ilData) = $this->lm->getTreatment($key, $bucketingKey, $feature, $attributes);
            $this->handleListener($key, $bucketingKey, $feature, $attributes, $treatment, $ilData);
            return $treatment;
        } catch (\Exception $exc) {
            $this->logger->error($exc);
            return "control";
        }
    }

    public function getTreatments(string $key, ?string $bucketingKey, array $features, ?array $attributes): array
    {
        try {
            $results = $this->lm->getTreatments($key, $bucketingKey, $features, $attributes);
            $toReturn = [];
            foreach ($results as $feature => $result) {
                list($treatment, $ilData) = $result;
                $toReturn[$feature] = $treatment;
                $this->handleListener($key, $bucketingKey, $feature, $attributes, $treatment, $ilData);
            }
            return $toReturn;
        } catch (\Exception $exc) {
            $this->logger->error($exc);
            return array_reduce($features, function ($r, $k) { $r[$k] = "control"; return $r; }, []);
        }
    }

    private function handleListener(string $key, ?string $bucketingKey, string $feature, ?array $attributes, string $treatment, ?ImpressionListenerData $ilData)
    {
        if ($this->impressionListener == null || $ilData == null) {
            return;
        }

        try {
            $this->impressionListener->accept(new Impression(
                $key,
                $bucketingKey,
                $feature,
                $treatment,
                $ilData->getLabel(),
                $ilData->getChangeNumber(),
                $ilData->getTimestamp()
            ), $attributes);
        } catch (\Exception $exc) {
            $this->logger->error("failed to invoke impressions listener:");
            $this->logger->error($exc);
        }
    }
}
