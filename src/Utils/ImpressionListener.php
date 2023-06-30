<?php

namespace SplitIO\ThinSdk\Utils;

use SplitIO\ThinSdk\Models\Impression;

interface ImpressionListener
{
    public function accept(Impression $impression, ?array $attributes);
}
