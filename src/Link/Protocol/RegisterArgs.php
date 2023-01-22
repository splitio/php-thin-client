<?php

namespace SplitIO\ThinClient\Link\Protocol;


enum RegisterArgs: int
{
	case ID          = 0;
	case SDK_VERSION = 1;
}
