<?php

/*
 * __        __    _                                    __  __  ____
 * \ \      / /_ _| |_ ___ _ __ _ __ ___   ___  ___ ___|  \/  |/ ___|
 *  \ \ /\ / / _` | __/ _ \ '__| '_ ` _ \ / _ \/ __/ __| |\/| | |
 *   \ V  V / (_| | ||  __/ |  | | | | | | (_) \__ \__ \ |  | | |___
 *    \_/\_/ \__,_|\__\___|_|  |_| |_| |_|\___/|___/___/_|  |_|\____|
 *
 * WatermossMC
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author WatermossMC Team
 * @link https://github.com/watermossmc/WatermossMC
 */

declare(strict_types=1);

namespace watermossmc\network\mcpe\protocol\clientbound;

use Socket;
use watermossmc\binary\McpeBinary;
use watermossmc\network\mcpe\protocol\Packet;
use watermossmc\network\mcpe\protocol\ProtocolInfo;
use watermossmc\network\mcpe\protocol\types\LevelSettings;
use watermossmc\network\Session;
use watermossmc\player\Player;
use watermossmc\util\Config;
use watermossmc\util\Logger;
use watermossmc\world\World;

final class StartGame extends Packet
{
    public static function send(
        Session $s,
        Socket $sock,
        World $world,
        Player $player
    ): void {
        $worldName = Config::getString(
            'motd',
            'WatermossMC Server'
        );

        $seed = (int) $world->seed;
        $spawn = $world->getSpawnPosition();
        $position = $player->getPosition();
        $rotation = $player->getRotation();
        $gameMode = $player->getGameMode();

        $payload = '';

        $xuid = $player->session->getXuid() ?? '0';

        $payload .= McpeBinary::writeSignedVarLong(
            (int) $xuid
        );

        $payload .= McpeBinary::writeUnsignedVarLong(
            $player->session->getRuntimeId()
        );

        $payload .= McpeBinary::writeSignedVarInt(
            $gameMode
        );

        $payload .= McpeBinary::writeFloat(
            (float) $position['x']
        );

        $payload .= McpeBinary::writeFloat(
            (float) $position['y']
        );

        $payload .= McpeBinary::writeFloat(
            (float) $position['z']
        );

        $payload .= McpeBinary::writeFloat(
            (float) $rotation['pitch']
        );

        $payload .= McpeBinary::writeFloat(
            (float) $rotation['yaw']
        );

        $payload .= LevelSettings::write(
            $seed,
            $spawn,
            $gameMode
        );

        $payload .= McpeBinary::writeString('');
        $payload .= McpeBinary::writeString($worldName);
        $payload .= McpeBinary::writeString('');
        $payload .= McpeBinary::writeBool(false);

        $payload .= McpeBinary::writeSignedVarInt(0);
        $payload .= McpeBinary::writeBool(true);

        $payload .= McpeBinary::writeLLong(0);
        $payload .= McpeBinary::writeSignedVarInt(0);

        $payload .= McpeBinary::writeUnsignedVarInt(0);

        $payload .= McpeBinary::writeString('');
        $payload .= McpeBinary::writeBool(true);
        $payload .= McpeBinary::writeString('WatermossMC');

        $payload .= "\x0A\x00\x00\x00";

        $payload .= McpeBinary::writeLLong(0);

        $payload .= McpeBinary::writeUUID(
            '00000000-0000-0000-0000-000000000000'
        );

        $payload .= McpeBinary::writeBool(false);
        $payload .= McpeBinary::writeBool(false);
        $payload .= McpeBinary::writeBool(false);

        $payload .= McpeBinary::writeBool(false);

        $payload .= McpeBinary::writeString('');
        $payload .= McpeBinary::writeString('');
        $payload .= McpeBinary::writeString('');
        $payload .= McpeBinary::writeString('');

        Logger::debug(
            'StartGame payload: ' . bin2hex($payload)
        );

        self::sendBatch(
            ProtocolInfo::START_GAME_PACKET,
            $payload,
            $s,
            $sock
        );
    }
}
