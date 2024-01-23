<?php

namespace SplitIO\ThinSdk;

use \SplitIO\ThinSdk\Utils\ImpressionListener;
use \SplitIO\ThinSdk\Utils\Tracing\TracingEventFactory as TEF;
use \SplitIO\ThinSdk\Utils\Tracing\Tracer;
use \SplitIO\ThinSdk\Utils\Tracing\NoOpTracerHook;
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
    private /*Tracer*/ $tracer;

    public function __construct(Manager $manager, LoggerInterface $logger, ?ImpressionListener $impressionListener, ?Cache $cache = null, ?Tracer $tracer = null)
    {
        $this->logger = $logger;
        $this->lm = $manager;
        $this->impressionListener = $impressionListener;
        $this->inputValidator = new InputValidator($logger);
        $this->cache = $cache ?? new NoCache();
        $this->tracer = $tracer ?? new Tracer(new NoOpTracerHook());
    }

    public function getTreatment(string $key, ?string $bucketingKey, string $feature, ?array $attributes = null): string
    {
        try {
            $id = $this->tracer->makeId();
            $method = Tracer::METHOD_GET_TREATMENT;
            $this->tracer->trace(TEF::forStart($method, $id, $this->tracer->includeArgs() ? func_get_args() : []));
            if (($fromCache = $this->cache->get($key, $feature, $attributes)) != null) {
                return $fromCache;
            }

            $this->tracer->trace(TEF::forRPCStart($method, $id));
            list($treatment, $ilData) = $this->lm->getTreatment($key, $bucketingKey, $feature, $attributes);
            $this->tracer->trace(TEF::forRPCEnd($method, $id));
            $this->handleListener($key, $bucketingKey, $feature, $attributes, $treatment, $ilData);
            $this->cache->set($key, $feature, $attributes, $treatment);
            return $treatment;
        } catch (\Exception $exc) {
            $this->tracer->trace(TEF::forException($method, $id, $exc));
            $this->logger->error($exc);
            return "control";
        } finally {
            $this->tracer->trace(TEF::forEnd($method, $id));
        }
    }

    public function getTreatments(string $key, ?string $bucketingKey, array $features, ?array $attributes = null): array
    {
        try {
            $id = $this->tracer->makeId();
            $method = Tracer::METHOD_GET_TREATMENTS;
            $this->tracer->trace(TEF::forStart($method, $id, $this->tracer->includeArgs() ? func_get_args() : []));
            // try to fetch items from cache. return result if all evaluations are cached
            // otherwise, send a Treatments RPC for missing ones and return merged result
            $toReturn = $this->cache->getMany($key, $features, $attributes);
            $features = self::getMissing($toReturn);
            if (count($features) == 0) {
                return $toReturn;
            }

            $this->tracer->trace(TEF::forRPCStart($method, $id));
            $results = $this->lm->getTreatments($key, $bucketingKey, $features, $attributes);
            $this->tracer->trace(TEF::forRPCEnd($method, $id));
            foreach ($results as $feature => $result) {
                list($treatment, $ilData) = $result;
                $toReturn[$feature] = $treatment;
                $this->handleListener($key, $bucketingKey, $feature, $attributes, $treatment, $ilData);
            }
            $this->cache->setMany($key, $attributes, $toReturn);
            return $toReturn;
        } catch (\Exception $exc) {
            $this->tracer->trace(TEF::forException($method, $id, $exc));
            $this->logger->error($exc);
            return array_reduce($features, function ($r, $k) {
                $r[$k] = "control";
                return $r;
            }, []);
        } finally {
            $this->tracer->trace(TEF::forEnd($method, $id));
        }
    }

    public function getTreatmentWithConfig(string $key, ?string $bucketingKey, string $feature, ?array $attributes = null): array
    {
        try {
            $id = $this->tracer->makeId();
            $method = Tracer::METHOD_GET_TREATMENT_WITH_CONFIG;
            $this->tracer->trace(TEF::forStart($method, $id, $this->tracer->includeArgs() ? func_get_args() : []));
            if (($fromCache = $this->cache->getWithConfig($key, $feature, $attributes)) != null) {
                return $fromCache;
            }

            $this->tracer->trace(TEF::forRPCStart($method, $id));
            list($treatment, $ilData, $config) = $this->lm->getTreatmentWithConfig($key, $bucketingKey, $feature, $attributes);
            $this->tracer->trace(TEF::forRPCEnd($method, $id));
            $this->handleListener($key, $bucketingKey, $feature, $attributes, $treatment, $ilData);
            $this->cache->setWithConfig($key, $feature, $attributes, $treatment, $config);
            return ['treatment' => $treatment, 'config' => $config];
        } catch (\Exception $exc) {
            $this->tracer->trace(TEF::forException($method, $id, $exc));
            $this->logger->error($exc);
            return ['treatment' => "control", 'config' => null];
        } finally {
            $this->tracer->trace(TEF::forEnd($method, $id));
        }
    }

    public function getTreatmentsWithConfig(string $key, ?string $bucketingKey, array $features, ?array $attributes = null): array
    {
        try {
            $id = $this->tracer->makeId();
            $method = Tracer::METHOD_GET_TREATMENTS_WITH_CONFIG;
            $this->tracer->trace(TEF::forStart($method, $id, $this->tracer->includeArgs() ? func_get_args() : []));
            $toReturn = $this->cache->getManyWithConfig($key, $features, $attributes);
            $features = self::getMissing($toReturn);
            if (count($features) == 0) {
                return $toReturn;
            }

            $this->tracer->trace(TEF::forRPCStart($method, $id));
            $results = $this->lm->getTreatmentsWithConfig($key, $bucketingKey, $features, $attributes);
            $this->tracer->trace(TEF::forRPCEnd($method, $id));
            foreach ($results as $feature => $result) {
                list($treatment, $ilData, $config) = $result;
                $toReturn[$feature] = ['treatment' => $treatment, 'config' => $config];
                $this->handleListener($key, $bucketingKey, $feature, $attributes, $treatment, $ilData);
            }
            $this->cache->setManyWithConfig($key, $attributes, $toReturn);
            return $toReturn;
        } catch (\Exception $exc) {
            $this->tracer->trace(TEF::forException($method, $id, $exc));
            $this->logger->error($exc);
            return array_reduce($features, function ($r, $k) {
                $r[$k] = ['treatment' => 'control', 'config' => null];
                return $r;
            }, []);
        } finally {
            $this->tracer->trace(TEF::forEnd($method, $id));
        }
    }

    public function getTreatmentsByFlagSet(
        string $key,
        ?string $bucketingKey,
        string $flagSet,
        ?array $attributes
    ): array {
        try {
            $id = $this->tracer->makeId();
            $method = Tracer::METHOD_GET_TREATMENTS_BY_FLAG_SET;
            $this->tracer->trace(TEF::forStart($method, $id, $this->tracer->includeArgs() ? func_get_args() : []));
            $flagSet = $this->inputValidator->sanitize($flagSet, 'getTreatmentsByFlagSet');
            if (is_null($flagSet)) {
                return array();
            }
            $featuresFromSet = $this->cache->getFeaturesByFlagSets([$flagSet]);
            if (!is_null($featuresFromSet)) {
                $toReturn = $this->cache->getMany($key, $featuresFromSet, $attributes);
                $features = self::getMissing($toReturn);
                if (count($features) == 0) {
                    return $toReturn;
                }
            }

            $this->tracer->trace(TEF::forRPCStart($method, $id));
            $results = $this->lm->getTreatmentsByFlagSet($key, $bucketingKey, $flagSet, $attributes);
            $this->tracer->trace(TEF::forRPCEnd($method, $id));
            $toReturn = array();
            foreach ($results as $feature => $result) {
                list($treatment, $ilData) = $result;
                $toReturn[$feature] = $treatment;
                $this->handleListener($key, $bucketingKey, $feature, $attributes, $treatment, $ilData);
            }
            $this->cache->setFeaturesForFlagSets([$flagSet], array_keys($results));
            $this->cache->setMany($key, $attributes, $toReturn);
            return $toReturn;
        } catch (\Exception $exc) {
            $this->tracer->trace(TEF::forException($method, $id, $exc));
            $this->logger->error($exc);
            return array();
        } finally {
            $this->tracer->trace(TEF::forEnd($method, $id));
        }
    }

    public function getTreatmentsWithConfigByFlagSet(
        string $key,
        ?string $bucketingKey,
        string $flagSet,
        ?array $attributes = null
    ): array {
        try {
            $id = $this->tracer->makeId();
            $method = Tracer::METHOD_GET_TREATMENTS_WITH_CONFIG_BY_FLAG_SET;
            $this->tracer->trace(TEF::forStart($method, $id, $this->tracer->includeArgs() ? func_get_args() : []));
            $flagSet = $this->inputValidator->sanitize($flagSet, 'getTreatmentsWithConfigByFlagSet');
            if (is_null($flagSet)) {
                return array();
            }
            $featuresFromSet = $this->cache->getFeaturesByFlagSets([$flagSet]);
            if (!is_null($featuresFromSet)) {
                $toReturn = $this->cache->getManyWithConfig($key, $featuresFromSet, $attributes);
                $features = self::getMissing($toReturn);
                if (count($features) == 0) {
                    return $toReturn;
                }
            }

            $this->tracer->trace(TEF::forRPCStart($method, $id));
            $results = $this->lm->getTreatmentsWithConfigByFlagSet($key, $bucketingKey, $flagSet, $attributes);
            $this->tracer->trace(TEF::forRPCEnd($method, $id));
            $toReturn = array();
            foreach ($results as $feature => $result) {
                list($treatment, $ilData, $config) = $result;
                $toReturn[$feature] = ['treatment' => $treatment, 'config' => $config];
                $this->handleListener($key, $bucketingKey, $feature, $attributes, $treatment, $ilData);
            }
            $this->cache->setFeaturesForFlagSets([$flagSet], array_keys($results));
            $this->cache->setManyWithConfig($key, $attributes, $toReturn);
            return $toReturn;
        } catch (\Exception $exc) {
            $this->tracer->trace(TEF::forException($method, $id, $exc));
            $this->logger->error($exc);
            return array();
        } finally {
            $this->tracer->trace(TEF::forEnd($method, $id));
        }
    }

    public function getTreatmentsByFlagSets(
        string $key,
        ?string $bucketingKey,
        array $flagSets,
        ?array $attributes
    ): array {
        try {
            $id = $this->tracer->makeId();
            $method = Tracer::METHOD_GET_TREATMENTS_BY_FLAG_SETS;
            $this->tracer->trace(TEF::forStart($method, $id, $this->tracer->includeArgs() ? func_get_args() : []));
            $flagSets = $this->inputValidator->sanitizeMany($flagSets, 'getTreatmentsByFlagSets');
            if (is_null($flagSets)) {
                return array();
            }
            $featuresFromSets = $this->cache->getFeaturesByFlagSets($flagSets);
            if (!is_null($featuresFromSets)) {
                $toReturn = $this->cache->getMany($key, $featuresFromSets, $attributes);
                $features = self::getMissing($toReturn);
                if (count($features) == 0) {
                    return $toReturn;
                }
            }

            $this->tracer->trace(TEF::forRPCStart($method, $id));
            $results = $this->lm->getTreatmentsByFlagSets($key, $bucketingKey, $flagSets, $attributes);
            $this->tracer->trace(TEF::forRPCEnd($method, $id));
            $toReturn = array();
            foreach ($results as $feature => $result) {
                list($treatment, $ilData) = $result;
                $toReturn[$feature] = $treatment;
                $this->handleListener($key, $bucketingKey, $feature, $attributes, $treatment, $ilData);
            }
            $this->cache->setFeaturesForFlagSets($flagSets, array_keys($results));
            $this->cache->setMany($key, $attributes, $toReturn);
            return $toReturn;
        } catch (\Exception $exc) {
            $this->tracer->trace(TEF::forException($method, $id, $exc));
            $this->logger->error($exc);
            return array();
        } finally {
            $this->tracer->trace(TEF::forEnd($method, $id));
        }
    }

    public function getTreatmentsWithConfigByFlagSets(
        string $key,
        ?string $bucketingKey,
        array $flagSets,
        ?array $attributes = null
    ): array {
        try {
            $id = $this->tracer->makeId();
            $method = Tracer::METHOD_GET_TREATMENTS_WITH_CONFIG_BY_FLAG_SETS;
            $this->tracer->trace(TEF::forStart($method, $id, $this->tracer->includeArgs() ? func_get_args() : []));
            $flagSets = $this->inputValidator->sanitizeMany($flagSets, 'getTreatmentsWithConfigByFlagSets');
            if (is_null($flagSets)) {
                return array();
            }
            $featuresFromSet = $this->cache->getFeaturesByFlagSets($flagSets);
            if (!is_null($featuresFromSet)) {
                $toReturn = $this->cache->getManyWithConfig($key, $featuresFromSet, $attributes);
                $features = self::getMissing($toReturn);
                if (count($features) == 0) {
                    return $toReturn;
                }
            }

            $this->tracer->trace(TEF::forRPCStart($method, $id));
            $results = $this->lm->getTreatmentsWithConfigByFlagSets($key, $bucketingKey, $flagSets, $attributes);
            $this->tracer->trace(TEF::forRPCEnd($method, $id));
            $toReturn = array();
            foreach ($results as $feature => $result) {
                list($treatment, $ilData, $config) = $result;
                $toReturn[$feature] = ['treatment' => $treatment, 'config' => $config];
                $this->handleListener($key, $bucketingKey, $feature, $attributes, $treatment, $ilData);
            }
            $this->cache->setFeaturesForFlagSets($flagSets, array_keys($results));
            $this->cache->setManyWithConfig($key, $attributes, $toReturn);
            return $toReturn;
        } catch (\Exception $exc) {
            $this->tracer->trace(TEF::forException($method, $id, $exc));
            $this->logger->error($exc);
            return array();
        } finally {
            $this->tracer->trace(TEF::forEnd($method, $id));
        }
    }

    public function track(string $key, string $trafficType, string $eventType, ?float $value = null, ?array $properties = null): bool
    {
        try {
            $id = $this->tracer->makeId();
            $method = Tracer::METHOD_TRACK;
            $this->tracer->trace(TEF::forStart($method, $id, $this->tracer->includeArgs() ? func_get_args() : []));
            $properties = $this->inputValidator->validProperties($properties);
            $this->tracer->trace(TEF::forRPCStart($method, $id));
            $res = $this->lm->track($key, $trafficType, $eventType, $value, $properties);
            $this->tracer->trace(TEF::forRPCEnd($method, $id));
            return $res;
        } catch (ValidationException $exc) {
            $this->tracer->trace(TEF::forException($method, $id, $exc));
            $this->logger->error("error validating event properties: " . $exc->getMessage());
        } catch (\Exception $exc) {
            $this->tracer->trace(TEF::forException($method, $id, $exc));
            $this->logger->error($exc);
        } finally {
            $this->tracer->trace(TEF::forEnd($method, $id));
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
