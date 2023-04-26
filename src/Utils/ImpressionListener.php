<?php

namespace SplitIO\ThinClient\Utils;

use SplitIO\ThinClient\Models\Impression;

interface ImpressionListener
{
    public function accept(Impression $impression);
}
