<?php

namespace SplitIO\ThinSdk;

use \SplitIO\ThinSdk\Utils\ImpressionListener;
use \SplitIO\ThinSdk\Utils\InputValidator\InputValidator;
use \SplitIO\ThinSdk\Utils\InputValidator\ValidationException;
use \SplitIO\ThinSdk\Models\Impression;
use \SplitIO\ThinSdk\Link\Consumer\Manager;
use \SplitIO\ThinSdk\Link\Protocol\V1\ImpressionListenerData;
use \Psr\Log\LoggerInterface;


class Client implements ClientInterface
{
    private /*Link\Consumer\Manager*/ $lm;
    private /*LoggerInterface*/ $logger;
    private /*?ImpressionListener*/ $impressionListener;
    private /*InputValidator*/ $inputValidator;

    public function __construct(Manager $manager, LoggerInterface $logger, ?ImpressionListener $impressionListener)
    {
        $this->logger = $logger;
        $this->lm = $manager;
        $this->impressionListener = $impressionListener;
        $this->inputValidator = new InputValidator($logger);
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

    public function track(string $key, string $trafficType, string $eventType, ?float $value, ?array $properties): bool
    {
        try {
            $properties = $this->inputValidator->validProperties($properties);
            var_dump($properties);
            return $this->lm->track($key, $trafficType, $eventType, $value, $properties);
        } catch (ValidationException $exc) {
            $this->logger->error("error validating event properties: " . $exc->getMessage());
        } catch (\Exception $exc) {
            $this->logger->error($exc);
        }

        return false;
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
