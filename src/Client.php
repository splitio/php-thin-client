<?php

namespace SplitIO\ThinClient;

use \SplitIO\ThinClient\Utils\ImpressionListener;
use \Psr\Log\LoggerInterface;

class Client implements ClientInterface
{
    private Link\Consumer\Manager $lm;
    private LoggerInterface $logger;
    private ?ImpressionListener $impressionListener;

    public function __construct(
        Link\Consumer\Manager $manager,
        LoggerInterface $logger,
        ?ImpressionListener $impressionListener,
    )
    {
        $this->logger = $logger;
        $this->lm = $manager;
        $this->impressionListener = $impressionListener;
    }

    public function getTreatment(string $key, ?string $bucketingKey, string $feature, ?array $attributes): string
    {
        try {
            $result = $this->lm->getTreatment($key, $bucketingKey, $feature, $attributes);
            if ($this->impressionListener != null && $result->getListenerData() != null) {
                $this->impressionListener->accept(new models\Impression(
                    $key,
                    $bucketingKey,
                    $feature,
                    $result->getTreatment(),
                    $result->getListenerData()->getLabel(),
                    $result->getListenerData()->getChangeNumber(),
                    $result->getListenerData()->getTimestamp()
                ), $attributes);
            }

            return $result->getTreatment();

        } catch (\Exception $exc) {
            $this->logger->error($exc);
            return "control";
        }
    }

}
