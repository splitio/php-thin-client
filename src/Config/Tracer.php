<?php

namespace SplitIO\ThinSdk\Config;

use SplitIO\ThinSdk\Utils\Tracing\TracerHook;


class Tracer
{

    private /*?TracerHook*/ $hook;
    private /*bool*/ $forwardArgs;

    private function __construct(?TracerHook $hook, bool $forwardArguments)
    {
        $this->hook = $hook;
        $this->forwardArgs = $forwardArguments;
    }

    public function hook(): ?TracerHook
    {
        return $this->hook;
    }

    public function forwardArgs(): bool
    {
        return $this->forwardArgs;
    }

    public static function fromArray(array $config): Tracer
    {
        $d = self::default();
        return new Tracer(
            $config['hook'] ?? $d->hook(),
            $config['forwardArgs'] ?? $d->forwardArgs,
        );
    }

    public static function default(): Tracer
    {
        return new Tracer(null, false);
    }
}
