<?php

declare(strict_types=1);

namespace WatermossMC\Minecraft\Packets;

use Socket;
use WatermossMC\Binary\Binary;
use WatermossMC\Network\Session;
use WatermossMC\Util\Config;

final class StartGame extends Packet
{
    public static function send(Session $s, Socket $sock): void
    {
        $worldName = Config::getString('level_name', 'PHP World');
        $serverName = Config::getString('server_name', 'PHP World');
        $gameModeName = Config::getString('gamemode', 'Survival');
        $gameModeId = Config::getInt('game_mode_id', self::gameModeToId($gameModeName));

        $p = Binary::writeVarLong(1);
        $p .= Binary::writeVarLong(1);
        $p .= Binary::writeInt(1);
        $p .= pack("g", 0.0) . pack("g", 64.0) . pack("g", 0.0);
        $p .= pack("g", 0.0) . pack("g", 0.0);
        $p .= Binary::writeInt(1);
        $p .= Binary::writeInt(1);
        $p .= Binary::writeInt(0) . Binary::writeInt(64) . Binary::writeInt(0);
        $p .= Binary::writeBool(false);
        $p .= Binary::writeInt(0);
        $p .= Binary::writeBool(false);
        $p .= Binary::writeBool(false);
        $p .= Binary::writeBool(false);
        $p .= Binary::writeString($worldName);
        $p .= Binary::writeInt(0);
        $p .= Binary::writeInt(0);
        $p .= Binary::writeBool(false);
        $p .= Binary::writeString("php");
        $p .= Binary::writeString($serverName);
        $p .= Binary::writeBool(false);
        $p .= Binary::writeBool(true);
        $p .= Binary::writeBool(true);

        self::sendBatch(0x0B, $p, $s, $sock);
    }

    private static function gameModeToId(string $gameMode): int
    {
        return match (strtolower($gameMode)) {
            'creative' => 0,
            'survival' => 1,
            'adventure' => 2,
            'spectator' => 3,
            default => 1,
        };
    }
}
