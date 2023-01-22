<?php

namespace SplitIO\ThinClient\Link\Protocol;

enum OpCode: int
{
	case Register = 0x00;

	case Treatment = 0x11;
	case Treatments = 0x12;
	case TreatmentWithConfig = 0x13;
	case TreatmentsWithConfig = 0x14;

	case Track = 0x80;
}
