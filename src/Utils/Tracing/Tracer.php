<?php

namespace SplitIO\ThinSdk\Utils\Tracing;

class Tracer
{
    public const METHOD_GET_TREATMENT = 10;
    public const METHOD_GET_TREATMENTS = 11;
    public const METHOD_GET_TREATMENT_WITH_CONFIG = 12;
    public const METHOD_GET_TREATMENTS_WITH_CONFIG = 13;
    public const METHOD_TRACK = 14;

    public const EVENT_START = 30;
    public const EVENT_RPC_START = 31;
    public const EVENT_RPC_END = 32;
    public const EVENT_END = 33;
    public const EVENT_EXCEPTION = 34;

    private /*TracerHook*/ $hook;
    private /*bool*/ $includeArgs;

    public function __construct(?TracerHook $hook, bool $includeArgs = false)
    {
        $this->hook = $hook ?? new NoOpTracerHook();
        $this->includeArgs = $includeArgs;
    }

    public function includeArgs(): bool
    {
        return $this->includeArgs;
    }

    public function trace(array $event)
    {
        $this->hook->on($event);
    }

    public function makeId(): string
    {
        return uniqid();
    }
}
