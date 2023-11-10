<?php

namespace SplitIO\ThinSdk\Config;

use SplitIO\ThinSdk\Utils\ImpressionListener;


class Utils
{
    private /*?ImpressionListener*/ $listener;
    private /*?string*/ $evaluationCache;
    private /*?TracerHook*/ $tracer;

    private function __construct(?ImpressionListener $listener, EvaluationCache $cache, Tracer $tracer)
    {
        $this->listener = $listener;
        $this->evaluationCache = $cache;
        $this->tracer = $tracer;
    }

    public function impressionListener(): ?ImpressionListener
    {
        return $this->listener;
    }

    public function tracer(): Tracer
    {
        return $this->tracer;
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
            Tracer::fromArray($config['__tracer'] ?? []),
        );
    }

    public static function default(): Utils
    {
        return new Utils(null, EvaluationCache::default(), Tracer::default());
    }
}
