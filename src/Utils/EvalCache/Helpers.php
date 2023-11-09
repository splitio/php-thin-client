<?php

namespace SplitIO\ThinSdk\Utils\EvalCache;

use SplitIO\ThinSdk\Config;
use Psr\Log\LoggerInterface;

class Helpers
{
    public static function getCache(Config\Utils $config, LoggerInterface $logger): Cache
    {
        $policy = self::getCacheEvictionPolicy($config);
        $cache = new NoCache();
        switch ($config->evaluationCache()) {
            case 'key-only':
                $cache = new CacheImpl(new KeyOnlyHasher(), $policy);
                break;
            case 'key-attributes':
                $cache = new CacheImpl(new KeyAttributeCRC32Hasher(), $policy);
                break;
            case 'custom':
                if (is_null($config->customCacheHash())) {
                    $logger->error(sprintf(
                        "config indicates 'custom' evaluation cache hasher, but no 'customCacheHash' passed. Cache will be disabled"
                    ));
                } else {
                    $cache = new CacheImpl($config->customCacheHash(), $policy);
                }
        }
        return $cache;
    }


    private static function getCacheEvictionPolicy(Config\Utils $config): EvictionPolicy
    {
        switch ($config->cacheEvictionPolicy()) {
            case 'random':
                return new RandomEviction($config->cacheMaxSize());
            case 'most-recent':
                return new MostRecentEviction($config->cacheMaxSize());
            default:
                return new NoEviction();
        }
    }
}
