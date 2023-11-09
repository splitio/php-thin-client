<?php

namespace SplitIO\ThinSdk\Utils\EvalCache;

use SplitIO\ThinSdk\Config;
use Psr\Log\LoggerInterface;

class Helpers
{
    public static function getCache(Config\EvaluationCache $config, LoggerInterface $logger): Cache
    {
        $policy = self::getCacheEvictionPolicy($config);
        $cache = new NoCache();
        switch ($config->type()) {
            case 'key-only':
                $cache = new CacheImpl(new KeyOnlyHasher(), $policy);
                break;
            case 'key-attributes':
                $cache = new CacheImpl(new KeyAttributeCRC32Hasher(), $policy);
                break;
            case 'custom':
                if (is_null($config->customHash())) {
                    $logger->error(sprintf(
                        "config indicates 'custom' evaluation cache hasher, but no 'customCacheHash' passed. Cache will be disabled"
                    ));
                } else {
                    $cache = new CacheImpl($config->customHash(), $policy);
                }
        }
        return $cache;
    }


    private static function getCacheEvictionPolicy(Config\EvaluationCache $config): EvictionPolicy
    {
        switch ($config->evictionPolicy()) {
            case 'random':
                return new RandomEviction($config->maxSize());
            default:
                return new NoEviction($config->maxSize());
        }
    }
}
