<?php

namespace SplitIO\ThinSdk\Link\Protocol\V1;

/*
enum TreatmentsByFlagSetArgs: int
{
    case KEY           = 0;
    case BUCKETING_KEY = 1;
    case FLAGSET       = 2;
    case ATTRIBUTES    = 3;
}
*/

use MyCLabs\Enum\Enum;

class TreatmentsByFlagSetArgs extends Enum
{
    private const KEY = 0;
    private const BUCKETING_KEY = 1;
    private const FLAG_SET = 2;
    private const ATTRIBUTES = 3;
}
