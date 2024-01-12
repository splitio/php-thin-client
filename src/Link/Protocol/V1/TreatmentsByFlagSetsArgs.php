<?php

namespace SplitIO\ThinSdk\Link\Protocol\V1;

/*
enum TreatmentsByFlagSetsArgs: int
{
    case KEY            = 0;
    case BUCKETING_KEY  = 1;
    case FLAGSETS       = 2;
    case ATTRIBUTES     = 3;
}
*/

use MyCLabs\Enum\Enum;

class TreatmentsByFlagSetsArgs extends Enum
{
    private const KEY = 0;
    private const BUCKETING_KEY = 1;
    private const FLAG_SETS = 2;
    private const ATTRIBUTES = 3;
}
