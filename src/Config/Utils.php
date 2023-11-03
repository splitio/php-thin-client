<?php

namespace SplitIO\ThinSdk\Config;

use SplitIO\ThinSdk\Utils\ImpressionListener;
use SplitIO\ThinSdk\Utils\EvalCache\InputHasher;


class Utils
{
    private /*?ImpressionListener*/ $listener;
    private /*?string*/ $evaluationCache;
    private /*?InputHasher*/ $customCacheHash;

    private function __construct(?ImpressionListener $listener, ?string $evaluationCache, ?InputHasher $customCacheHash)
    {
        $this->listener = $listener;
        $this->evaluationCache = $evaluationCache;
        $this->customCacheHash = $customCacheHash;
    }

    public function impressionListener(): ?ImpressionListener
    {
        return $this->listener;
    }

    public function evaluationCache(): ?string
    {
        return $this->evaluationCache;
    }

    public function customCacheHash(): ?InputHasher
    {
        return $this->customCacheHash;
    }

    public static function fromArray(array $config): Utils
    {
        $d = self::default();
        return new Utils(
            $config['impressionListener'] ?? $d->impressionListener(),
            $config['evaluationCache'] ?? $d->evaluationCache(),
            $config['customCacheHash'] ?? $d->customCacheHash(),
        );
    }

    public static function default(): Utils
    {
        return new Utils(null, 'none', null);
    }
}
