<?php

namespace SplitIO\ThinSdk\Utils\Tracing;

class TracingEventFactory
{
    public static function forStart(int $method, string $id, ?array $arguments): array
    {
        $base = ['id' => $id, 'method' => $method, 'event' => Tracer::EVENT_START];
        return is_null($arguments)
            ? $base
            : array_merge($base, ['arguments' => $arguments]);
    }

    public static function forRPCStart(int $method, string $id): array
    {
        return ['id' => $id, 'method' => $method, 'event' => Tracer::EVENT_RPC_START];
    }

    public static function forRPCEnd(int $method, string $id): array
    {
        return ['id' => $id, 'method' => $method, 'event' => Tracer::EVENT_RPC_END];
    }

    public static function forException(int $method, string $id, \Exception $exception): array
    {
        return [
            'id' => $id,
            'method' => $method,
            'event' => Tracer::EVENT_EXCEPTION,
            'exception' => $exception,
        ];
    }

    public static function forEnd(int $method, string $id): array
    {
        return [
            'id' => $id,
            'method' => $method,
            'event' => Tracer::EVENT_END,
        ];
    }
}
