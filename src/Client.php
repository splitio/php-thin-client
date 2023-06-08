<?php

namespace SplitIO\ThinClient;

use \SplitIO\ThinClient\Utils\ImpressionListener;
use \SplitIO\ThinClient\Models\Impression;
use \SplitIO\ThinClient\Link\Consumer\Manager;
use \SplitIO\ThinClient\Link\Protocol\V1\TreatmentResponse;
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
            $result = $this->lm->getTreatment($key, $bucketingKey, $feature, $attributes);
            $this->handleListener($key, $bucketingKey, $feature, $attributes, $result);
            return $result->getTreatment();
        } catch (\Exception $exc) {
            $this->logger->error($exc);
            return "control";
        }
    }

    private function handleListener(string $key, ?string $bucketingKey, string $feature, ?array $attributes, TreatmentResponse $result)
    {
        if ($this->impressionListener == null || $result->getListenerData() == null) {
            return;
        }

        try {
            $this->impressionListener->accept(new Impression(
                $key,
                $bucketingKey,
                $feature,
                $result->getTreatment(),
                $result->getListenerData()->getLabel(),
                $result->getListenerData()->getChangeNumber(),
                $result->getListenerData()->getTimestamp()
            ), $attributes);
        } catch (\Exception $exc) {
            $this->logger->error("failed to invoke impressions listener:");
            $this->logger->error($exc);
        }
    }
}
