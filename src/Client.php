<?php

namespace SplitIO\ThinSdk;

use \SplitIO\ThinSdk\Utils\ImpressionListener;
use \SplitIO\ThinSdk\Utils\EvalCache\Cache;
use \SplitIO\ThinSdk\Utils\EvalCache\NoCache;
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
    private /*Cache*/ $cache;

    public function __construct(Manager $manager, LoggerInterface $logger, ?ImpressionListener $impressionListener, ?Cache $cache = null)
    {
        $this->logger = $logger;
        $this->lm = $manager;
        $this->impressionListener = $impressionListener;
        $this->inputValidator = new InputValidator($logger);
        $this->cache = $cache ?? new NoCache();
    }

    public function getTreatment(string $key, ?string $bucketingKey, string $feature, ?array $attributes = null): string
    {
        try {
            if (($fromCache = $this->cache->get($key, $feature, $attributes)) != null) {
                return $fromCache;
            }

            list($treatment, $ilData) = $this->lm->getTreatment($key, $bucketingKey, $feature, $attributes);
            $this->handleListener($key, $bucketingKey, $feature, $attributes, $treatment, $ilData);
            $this->cache->set($key, $feature, $attributes, $treatment);
            return $treatment;
        } catch (\Exception $exc) {
            $this->logger->error($exc);
            return "control";
        }
    }

    public function getTreatments(string $key, ?string $bucketingKey, array $features, ?array $attributes = null): array
    {
        try {
            // try to fetch items from cache. return result if all evaluations are cached
            // otherwise, send a Treatments RPC for missing ones and return merged result
            $toReturn = $this->cache->getMany($key, $features, $attributes);
            $features = self::getMissing($toReturn);
            if (count($features) == 0) {
                return $toReturn;
            }

            $results = $this->lm->getTreatments($key, $bucketingKey, $features, $attributes);
            foreach ($results as $feature => $result) {
                list($treatment, $ilData) = $result;
                $toReturn[$feature] = $treatment;
                $this->handleListener($key, $bucketingKey, $feature, $attributes, $treatment, $ilData);
            }
            $this->cache->setMany($key, $attributes, $toReturn);
            return $toReturn;
        } catch (\Exception $exc) {
            $this->logger->error($exc);
            return array_reduce($features, function ($r, $k) {
                $r[$k] = "control";
                return $r;
            }, []);
        }
    }

    public function getTreatmentWithConfig(string $key, ?string $bucketingKey, string $feature, ?array $attributes = null): array
    {
        try {

            if (($fromCache = $this->cache->getWithConfig($key, $feature, $attributes)) != null) {
                return $fromCache;
            }

            list($treatment, $ilData, $config) = $this->lm->getTreatmentWithConfig($key, $bucketingKey, $feature, $attributes);
            $this->handleListener($key, $bucketingKey, $feature, $attributes, $treatment, $ilData);
            $this->cache->setWithConfig($key, $feature, $attributes, $treatment, $config);
            return ['treatment' => $treatment, 'config' => $config];
        } catch (\Exception $exc) {
            $this->logger->error($exc);
            return "control";
        }
    }

    public function getTreatmentsWithConfig(string $key, ?string $bucketingKey, array $features, ?array $attributes = null): array
    {
        try {
            $toReturn = $this->cache->getManyWithConfig($key, $features, $attributes);
            $features = self::getMissing($toReturn);

            if (count($features) == 0) {
                return $toReturn;
            }

            $results = $this->lm->getTreatmentsWithConfig($key, $bucketingKey, $features, $attributes);
            foreach ($results as $feature => $result) {
                list($treatment, $ilData, $config) = $result;
                $toReturn[$feature] = ['treatment' => $treatment, 'config' => $config];
                $this->handleListener($key, $bucketingKey, $feature, $attributes, $treatment, $ilData);
            }
            $this->cache->setManyWithConfig($key, $attributes, $toReturn);
            return $toReturn;
        } catch (\Exception $exc) {
            $this->logger->error($exc);
            return array_reduce($features, function ($r, $k) {
                $r[$k] = ['treatment' => 'control', 'config' => null];
                return $r;
            }, []);
        }
    }

    public function track(string $key, string $trafficType, string $eventType, ?float $value = null, ?array $properties = null): bool
    {
        try {
            $properties = $this->inputValidator->validProperties($properties);
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

    private static function getMissing(array $results): array
    {
        return array_keys(array_filter($results, 'is_null'));
    }
}
