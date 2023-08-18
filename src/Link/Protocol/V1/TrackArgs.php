<?php

namespace SplitIO\ThinSdk\Link\Protocol\V1;

/*
enum TreatmentArgs: int
{
    case KEY            = 0;
    case TRAFFIC_TYPE   = 1;
    case EVENT_TYPE     = 2;
    case VALUE          = 3;
    case PROPERTIES     = 4;
}
 */

use MyCLabs\Enum\Enum;

class TrackArgs extends Enum
{
    private const KEY           = 0;
    private const TRAFFIC_TYPE  = 1;
    private const EVENT_TYPE    = 2;
    private const VALUE         = 3;
    private const PROPERTIES    = 4;
}
