<?php

declare(strict_types=1);

namespace watermossmc\mcpe\protocol;

use Socket;
use watermossmc\binary\Binary;
use watermossmc\mcpe\network\Session;

final class CraftingData extends Packet
{
    public static function sendEmpty(Session $s, Socket $sock): void
    {
        $payload = '';
        $payload .= Binary::writeVarInt(0); // recipesWithTypeIds count
        $payload .= Binary::writeVarInt(0); // potionTypeRecipes count
        $payload .= Binary::writeVarInt(0); // potionContainerRecipes count
        $payload .= Binary::writeVarInt(0); // materialReducerRecipes count
        $payload .= Binary::writeBool(true); // cleanRecipes

        self::sendBatch(ProtocolInfo::CRAFTING_DATA_PACKET, $payload, $s, $sock);
    }
}
