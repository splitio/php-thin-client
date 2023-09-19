<?php

namespace SplitIO\ThinSdk;

use SplitIO\ThinSdk\SplitView;

interface ManagerInterface
{
    function splitNames(): array;
    function split(string $name): ?SplitView;
    function splits(): array;
}
