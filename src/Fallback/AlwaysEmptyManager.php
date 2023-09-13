<?php

namespace SplitIO\ThinSdk\Fallback;

use SplitIO\ThinSdk\ManagerInterface;
use SplitIO\ThinSdk\SplitView;

class AlwaysEmptyManager implements ManagerInterface
{
    public function splitNames(): array
    {
        return [];
    }

    public function split(string $name): ?SplitView
    {
        return null;
    }

    public function splits(): array
    {
        return [];
    }
}
