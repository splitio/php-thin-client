<?php

namespace SplitIO\ThinSdk\Config;

use SplitIO\ThinSdk\Utils\ImpressionListener;


class Utils
{
    private /*?ImpressionListener*/ $listener;
    private /*?string*/ $evaluationCache;

    private function __construct(?ImpressionListener $listener, EvaluationCache $cache)
    {
        $this->listener = $listener;
        $this->evaluationCache = $cache;
    }

    public function impressionListener(): ?ImpressionListener
    {
        return $this->listener;
    }

    public function evaluationCache(): ?EvaluationCache
    {
        return $this->evaluationCache;
    }

    public static function fromArray(array $config): Utils
    {
        $d = self::default();
        return new Utils(
            $config['impressionListener'] ?? $d->impressionListener(),
            EvaluationCache::fromArray($config['evaluationCache'] ?? []),
        );
    }

    public static function default(): Utils
    {
        return new Utils(null, EvaluationCache::default());
    }
}
