<?php

namespace SplitIO\ThinClient\Link\Protocol\V1;

/*
enum RegisterArgs: int
{
    case ID          = 0;
    case SDK_VERSION = 1;
    case FLAGS       = 2;
}
 */

use MyCLabs\Enum\Enum;

class RegisterArgs extends Enum
{
    private const ID = 0;
    private const SDK_VERSION = 1;
    private const FLAGS = 2;
}

