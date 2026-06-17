<?php

declare(strict_types=1);

namespace WatermossMC\Minecraft\Packets\Types;

use WatermossMC\Binary\Binary;

final class Experiments
{
    public static function writeEmpty(): string
    {
        $p  = Binary::writeLInt(0);    // count experiments
        $p .= Binary::writeBool(false); // hasPreviouslyUsedExperiments
        return $p;
    }
}