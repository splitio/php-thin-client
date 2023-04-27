<?php

namespace SplitIO\ThinClient\Link\Protocol\V1;

/*
enum TreatmentArgs: int
{
    case KEY           = 0;
    case BUCKETING_KEY = 1;
    case FEATURE       = 2;
    case ATTRIBUTES    = 3;
}
 */

use MyCLabs\Enum\Enum;

class TreatmentArgs extends Enum
{
    private const KEY = 0;
    private const BUCKETING_KEY = 1;
    private const FEATURE = 2;
    private const ATTRIBUTES = 3;

}

