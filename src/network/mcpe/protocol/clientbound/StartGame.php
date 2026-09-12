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
    public static function send(Session $s, Socket $sock, World $world, Player $player): void
    {
        $worldName = Config::getString("motd", "WatermossMC Server");
        $seed = $world->seed;
        $spawn = $world->getSpawnPosition();
        $position = $player->getPosition();
        $rotation = $player->getRotation();

        $payload = '';

        $xuid = $player->session->getXuid() ?? '0';
        $payload .= McpeBinary::writeSignedVarLong((int) $xuid); // actorUniqueId
        $payload .= McpeBinary::writeUnsignedVarLong($player->session->getRuntimeId()); // actorRuntimeId
        $payload .= McpeBinary::writeSignedVarInt($player->getGameMode()); // playerGamemode

        // --- Player Position & Rotation ---
        $payload .= McpeBinary::writeFloat($position['x']);
        $payload .= McpeBinary::writeFloat($position['y']);
        $payload .= McpeBinary::writeFloat($position['z']);
        $payload .= McpeBinary::writeFloat($rotation['pitch']);
        $payload .= McpeBinary::writeFloat($rotation['yaw']);

        LevelSettings::write();

        $payload .= McpeBinary::writeString(""); // levelId
        $payload .= McpeBinary::writeString($worldName); // worldName
        $payload .= McpeBinary::writeString(""); // premiumWorldTemplateId
        $payload .= McpeBinary::writeBool(false); // isTrial

        // PlayerMovementSettings
        $payload .= McpeBinary::writeSignedVarInt(0); // rewindHistorySize
        $payload .= McpeBinary::writeBool(true); // serverAuthoritativeBlockBreaking

        $payload .= McpeBinary::writeLLong(0); // currentTick
        $payload .= McpeBinary::writeSignedVarInt(0); // enchantmentSeed

        // Block Palette
        $payload .= McpeBinary::writeUnsignedVarInt(0); // blockPalette Count

        // Server Info
        $payload .= McpeBinary::writeString(""); // multiplayerCorrelationId
        $payload .= McpeBinary::writeBool(true); // enableNewInventorySystem
        $payload .= McpeBinary::writeString("WatermossMC"); // serverSoftwareVersion

        // Player Actor Properties
        $payload .= "\x0a\x00\x00";

        $payload .= McpeBinary::writeLLong(0); // blockPaletteChecksum
        $payload .= McpeBinary::writeUUID("00000000-0000-0000-0000-000000000000"); // worldTemplateId
        $payload .= McpeBinary::writeBool(false); // enableClientSideChunkGeneration
        $payload .= McpeBinary::writeBool(false); // blockNetworkIdsAreHashes

        // NetworkPermissions
        $payload .= McpeBinary::writeBool(false); // serverAuthSounds

        // ServerJoinInformation
        $payload .= McpeBinary::writeBool(false);

        // ServerTelemetryData
        $payload .= McpeBinary::writeString(""); // serverId
        $payload .= McpeBinary::writeString(""); // scenarioId
        $payload .= McpeBinary::writeString(""); // worldId
        $payload .= McpeBinary::writeString(""); // ownerId

        Logger::debug(bin2hex($payload));

        self::sendBatch(ProtocolInfo::START_GAME_PACKET, $payload, $s, $sock);
    }
}
