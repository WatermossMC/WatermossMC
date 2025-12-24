<?php

declare(strict_types=1);

namespace WatermossMC\Minecraft\Packets\Types;

use WatermossMC\Binary\Binary;

final class Experiments
{
    public static function writeEmpty(): string
    {
        $p = Binary::writeVarInt(0);   // experiment count
        $p .= Binary::writeBool(false); // experimentsPreviouslyToggled
        return $p;
    }
}
