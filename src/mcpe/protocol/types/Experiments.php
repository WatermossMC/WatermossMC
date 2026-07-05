<?php

declare(strict_types=1);

namespace watermossmc\mcpe\protocol\types;

use watermossmc\binary\Binary;

final class Experiments
{
    public static function writeEmpty(): string
    {
        $p = Binary::writeLInt(0);    // count experiments
        $p .= Binary::writeBool(false); // hasPreviouslyUsedExperiments
        return $p;
    }
}
