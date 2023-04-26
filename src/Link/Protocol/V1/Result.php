<?php

namespace SplitIO\ThinClient\Link\Protocol\V1;

enum Result: int
{
    case Ok = 0x01;
    case InternalError = 0x10;
}
