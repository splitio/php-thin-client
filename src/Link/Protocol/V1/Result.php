<?php

namespace SplitIO\ThinSdk\Link\Protocol\V1;

/*
enum Result: int
{
    case Ok = 0x01;
    case InternalError = 0x10;
}
 */

use MyCLabs\Enum\Enum;

class Result extends Enum
{
    private const Ok = 0x01;
    private const InternalError = 0x10;
}

