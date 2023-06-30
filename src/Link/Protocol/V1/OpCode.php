<?php

namespace SplitIO\ThinSdk\Link\Protocol\V1;

/*
enum OpCode: int
{
    case Register = 0x00;

    case Treatment = 0x11;
    case Treatments = 0x12;
    case TreatmentWithConfig = 0x13;
    case TreatmentsWithConfig = 0x14;

    case Track = 0x80;
}
 */

use MyCLabs\Enum\Enum;

class OpCode extends Enum
{
    private const Register = 0x00;

    private const Treatment = 0x11;
    private const Treatments = 0x12;
    private const TreatmentWithConfig = 0x13;
    private const TreatmentsWithConfig = 0x14;

    private const Track = 0x80;

}

