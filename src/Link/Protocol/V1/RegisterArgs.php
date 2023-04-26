<?php

namespace SplitIO\ThinClient\Link\Protocol\V1;


enum RegisterArgs: int
{
    case ID          = 0;
    case SDK_VERSION = 1;
    case FLAGS       = 2;
}
