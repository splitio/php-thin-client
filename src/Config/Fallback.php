<?php

namespace SplitIO\ThinSdk\Config;

use SplitIO\ThinSdk\ClientInterface;
use SplitIO\ThinSdk\ManagerInterface;
use SplitIO\ThinSdk\Fallback\AlwaysControlClient;
use SplitIO\ThinSdk\Fallback\AlwaysEmptyManager;


class Fallback
{
    private /*bool*/ $disable;
    private /*ClientInterface*/ $customUserClient;
    private /*ManagerInterface*/ $customUserManager;

    private function __construct(bool $disable, ?ClientInterface $client, ?ManagerInterface $manager)
    {
        $this->disable = $disable;
        $this->customUserClient = $client;
        $this->customUserManager = $manager;
    }

    public function disable(): bool
    {
        return $this->disable;
    }

    public function client(): ?ClientInterface
    {
        return $this->customUserClient;
    }

    public function manager(): ?ManagerInterface
    {
        return $this->customUserManager;
    }

    public static function fromArray(array $config): Fallback
    {
        $d = self::default();
        return new Fallback(
            $config['disable'] ?? $d->disable(),
            $config['client'] ?? $d->client(),
            $config['manager'] ?? $d->manager()
        );
    }

    public static function default(): Fallback
    {
        return new Fallback(false, new AlwaysControlClient(), new AlwaysEmptyManager());
    }
}
