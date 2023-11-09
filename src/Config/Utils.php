<?php

namespace SplitIO\ThinSdk\Config;

use SplitIO\ThinSdk\Utils\ImpressionListener;
use SplitIO\ThinSdk\Utils\EvalCache\InputHasher;


class Utils
{
    private /*?ImpressionListener*/ $listener;
    private /*?string*/ $evaluationCache;
    private /*?InputHasher*/ $customCacheHash;
    private /*string*/ $cacheEvictionPolicy;
    private /*int*/ $cacheMaxSize;

    private function __construct(?ImpressionListener $listener, string $evaluationCache, ?InputHasher $customCacheHash, string $cacheEvictionPolicy, int $cacheMaxSize)
    {
        $this->listener = $listener;
        $this->evaluationCache = $evaluationCache;
        $this->customCacheHash = $customCacheHash;
        $this->cacheEvictionPolicy = $cacheEvictionPolicy;
        $this->cacheMaxSize = $cacheMaxSize;
    }

    public function impressionListener(): ?ImpressionListener
    {
        return $this->listener;
    }

    public function evaluationCache(): string
    {
        return $this->evaluationCache;
    }

    public function customCacheHash(): ?InputHasher
    {
        return $this->customCacheHash;
    }

    public function cacheEvictionPolicy(): string
    {
        return $this->cacheEvictionPolicy;
    }

    public function cacheMaxSize(): int
    {
        return $this->cacheMaxSize;
    }

    public static function fromArray(array $config): Utils
    {
        $d = self::default();
        return new Utils(
            $config['impressionListener'] ?? $d->impressionListener(),
            $config['evaluationCache'] ?? $d->evaluationCache(),
            $config['customCacheHash'] ?? $d->customCacheHash(),
            $config['cacheEvictionPolicy'] ?? $d->cacheEvictionPolicy(),
            $config['cacheMaxSize'] ?? $d->cacheMaxSize(),
        );
    }

    public static function default(): Utils
    {
        return new Utils(null, 'none', null, 'no-eviction', 2000);
    }
}
