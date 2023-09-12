<?php

namespace SplitIO\ThinSdk\Link\Protocol\V1;

/*
enum TreatmentArgs: int
{
    case SPLIT_SNAME    = 0;
}
 */

use MyCLabs\Enum\Enum;

class SplitArgs extends Enum
{
    private const SPLIT_NAME = 0;
}
