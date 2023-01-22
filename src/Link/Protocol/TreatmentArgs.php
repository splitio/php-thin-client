<?php

namespace SplitIO\ThinClient\Link\Protocol;


enum TreatmentArgs: int
{
	case KEY           = 0;
	case BUCKETING_KEY = 1;
	case FEATURE       = 2;
	case ATTRIBUTES    = 3;
}
