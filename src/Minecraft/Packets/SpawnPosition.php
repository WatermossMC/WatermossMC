<?php

declare(strict_types=1);

namespace WatermossMC\Minecraft\Packets;

use Socket;
use WatermossMC\Binary\Binary;
use WatermossMC\Binary\McpeBinary;
use WatermossMC\Network\Session;

final class SpawnPosition extends Packet
{
    public const SPAWN_TYPE_PLAYER_SPAWN = 0;
    public const SPAWN_TYPE_WORLD_SPAWN  = 1;

    public static function send(Session $s, Socket $sock, int $x = 0, int $y = 64, int $z = 0): void
    {
        $p  = McpeBinary::writeVarInt(self::SPAWN_TYPE_WORLD_SPAWN);

        $p .= McpeBinary::writeSignedVarInt($x); // X — signed
        $p .= McpeBinary::writeVarInt($y);       // Y — unsigned
        $p .= McpeBinary::writeSignedVarInt($z); // Z — signed

        $p .= McpeBinary::writeSignedVarInt($x);
        $p .= McpeBinary::writeVarInt($y);
        $p .= McpeBinary::writeSignedVarInt($z);

        $p .= McpeBinary::writeVarInt(0);        // dimensionId
        $p .= Binary::writeBool(false);          // spawnForced

        self::sendBatch(ProtocolInfo::SPAWN_POSITION_PACKET, $p, $s, $sock);
    }
}