<?php

namespace SplitIO\ThinSdk\Config;

use SplitIO\ThinSdk\Utils\EvalCache\InputHasher;


class EvaluationCache
{
    private /*?string*/ $type;
    private /*?InputHasher*/ $customHash;
    private /*string*/ $evictionPolicy;
    private /*int*/ $maxSize;

    private function __construct(string $type, ?InputHasher $customHash, string $evictionPolicy, int $maxSize)
    {
        $this->type = $type;
        $this->customHash = $customHash;
        $this->evictionPolicy = $evictionPolicy;
        $this->maxSize = $maxSize;
    }

    public function type(): string
    {
        return $this->type;
    }

    public function customHash(): ?InputHasher
    {
        return $this->customHash;
    }

    public function evictionPolicy(): string
    {
        return $this->evictionPolicy;
    }

    public function maxSize(): int
    {
        return $this->maxSize;
    }

    public static function fromArray(array $config): EvaluationCache
    {
        $d = self::default();
        return new EvaluationCache(
            $config['type'] ?? $d->type(),
            $config['customHash'] ?? $d->customHash(),
            $config['evictionPolicy'] ?? $d->evictionPolicy(),
            $config['maxSize'] ?? $d->maxSize(),
        );
    }

    public static function default(): EvaluationCache
    {
        return new EvaluationCache('none', null, 'no-eviction', 1000);
    }
}
