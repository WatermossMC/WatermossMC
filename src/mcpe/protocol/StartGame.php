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

declare (strict_types=1);

namespace watermossmc\mcpe\protocol;

use function count;

use Socket;
use watermossmc\binary\McpeBinary;
use watermossmc\mcpe\network\Session;
use watermossmc\mcpe\protocol\types\Experiments;
use watermossmc\player\Player;
use watermossmc\util\Config;
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
        // Actor IDs
        // actorUniqueId must be the XUID (unique permanent ID), NOT the runtimeId
        $xuid = $player->session->getXuid() ?? '0';
        $payload .= McpeBinary::writeSignedVarLong((int) $xuid);
        // actorRuntimeId is the session-specific ID
        $payload .= McpeBinary::writeUnsignedVarLong($player->session->getRuntimeId());
        // playerGamemode - Signed VarInt
        $payload .= McpeBinary::writeSignedVarInt($player->getGameMode());
        // playerGamemode
        // Player Position & Rotation
        $payload .= McpeBinary::writeFloat($position['x']);
        $payload .= McpeBinary::writeFloat($position['y']);
        $payload .= McpeBinary::writeFloat($position['z']);
        $payload .= McpeBinary::writeFloat($rotation['pitch']);
        $payload .= McpeBinary::writeFloat($rotation['yaw']);
        // LevelSettings
        $payload .= McpeBinary::writeLLong($seed);
        // seed
        // SpawnSettings
        $payload .= McpeBinary::writeLShort(0);
        // biomeType = DEFAULT
        $payload .= McpeBinary::writeString('');
        // biome name
        $payload .= McpeBinary::writeSignedVarInt(0);
        // dimension = overworld
        $payload .= McpeBinary::writeSignedVarInt(1);
        // generator
        $payload .= McpeBinary::writeSignedVarInt($world->getGameType());
        // worldGamemode
        $payload .= McpeBinary::writeBool(false);
        // hardcore
        $payload .= McpeBinary::writeSignedVarInt(1);
        // difficulty
        $payload .= McpeBinary::writeSignedVarInt($spawn['x']);
        // spawnPosition x
        $payload .= McpeBinary::writeSignedVarInt($spawn['y']);
        // spawnPosition y
        $payload .= McpeBinary::writeSignedVarInt($spawn['z']);
        // spawnPosition z
        $payload .= McpeBinary::writeBool(true);
        // hasAchievementsDisabled
        $payload .= McpeBinary::writeSignedVarInt(0);
        // editorWorldType = NON_EDITOR
        $payload .= McpeBinary::writeBool(false);
        // createdInEditorMode
        $payload .= McpeBinary::writeBool(false);
        // exportedFromEditorMode
        $payload .= McpeBinary::writeSignedVarInt(-1);
        // time
        $payload .= McpeBinary::writeSignedVarInt(0);
        // eduEditionOffer
        $payload .= McpeBinary::writeBool(false);
        // hasEduFeaturesEnabled
        $payload .= McpeBinary::writeString('');
        // eduProductUUID
        $payload .= McpeBinary::writeFloat(0.0);
        // rainLevel
        $payload .= McpeBinary::writeFloat(0.0);
        // lightningLevel
        $payload .= McpeBinary::writeBool(false);
        // hasConfirmedPlatformLockedContent
        $payload .= McpeBinary::writeBool(true);
        // isMultiplayerGame
        $payload .= McpeBinary::writeBool(true);
        // hasLANBroadcast
        $payload .= McpeBinary::writeSignedVarInt(0);
        // xboxLiveBroadcastMode
        $payload .= McpeBinary::writeSignedVarInt(0);
        // platformBroadcastMode
        $payload .= McpeBinary::writeBool(true);
        // commandsEnabled
        $payload .= McpeBinary::writeBool(true);
        // texturePacksRequired
        // GameRules
        $payload .= McpeBinary::writeVarInt(0);
        // count
        // Experiments
        $payload .= McpeBinary::writeLInt(0);
        // experiments count (LE unsigned int)
        $payload .= McpeBinary::writeBool(false);
        // hasPreviouslyUsedExperiments
        $payload .= McpeBinary::writeBool(false);
        // hasBonusChestEnabled
        $payload .= McpeBinary::writeBool(false);
        // hasStartWithMapEnabled
        $payload .= McpeBinary::writeSignedVarInt(0);
        // defaultPlayerPermission
        $payload .= McpeBinary::writeLInt(4);
        // serverChunkTickRadius
        $payload .= McpeBinary::writeBool(false);
        // hasLockedBehaviorPack
        $payload .= McpeBinary::writeBool(false);
        // hasLockedResourcePack
        $payload .= McpeBinary::writeBool(false);
        // isFromLockedWorldTemplate
        $payload .= McpeBinary::writeBool(false);
        // useMsaGamertagsOnly
        $payload .= McpeBinary::writeBool(false);
        // isFromWorldTemplate
        $payload .= McpeBinary::writeBool(false);
        // isWorldTemplateOptionLocked
        $payload .= McpeBinary::writeBool(false);
        // onlySpawnV1Villagers
        $payload .= McpeBinary::writeBool(false);
        // disablePersona
        $payload .= McpeBinary::writeBool(false);
        // disableCustomSkins
        $payload .= McpeBinary::writeBool(false);
        // muteEmoteAnnouncements
        $payload .= McpeBinary::writeString("1.20.0");
        // vanillaVersion
        $payload .= McpeBinary::writeLInt(0);
        // limitedWorldWidth
        $payload .= McpeBinary::writeLInt(0);
        // limitedWorldLength
        $payload .= McpeBinary::writeBool(true);
        // isNewNether
        // Optional EduSharedUriResource
        $payload .= McpeBinary::writeBool(false);
        // Optional experimentalGameplayOverride
        $payload .= McpeBinary::writeBool(false);
        $payload .= McpeBinary::writeByte(0);
        // chatRestrictionLevel
        $payload .= McpeBinary::writeBool(false);
        // disablePlayerInteractions
        $payload .= McpeBinary::writeSignedVarInt(0);
        // serverEditorConnectionPolicy
        $payload .= McpeBinary::writeBool(false);
        // allowAnonymousBlockDropsInEditorWorlds
        // World Identification
        $payload .= McpeBinary::writeString("");
        // levelId
        $payload .= McpeBinary::writeString($worldName);
        // worldName
        $payload .= McpeBinary::writeString("");
        // premiumWorldTemplateId
        $payload .= McpeBinary::writeBool(false);
        // isTrial
        // PlayerMovementSettings
        $payload .= McpeBinary::writeSignedVarInt(0);
        // rewindHistorySize
        $payload .= McpeBinary::writeBool(false);
        // serverAuthoritativeBlockBreaking
        // Tick & Seed
        $payload .= McpeBinary::writeLLong(0);
        // currentTick
        $payload .= McpeBinary::writeSignedVarInt(0);
        // enchantmentSeed
        // Block Palette
        $payload .= McpeBinary::writeVarInt(0);
        // count
        // Server Info
        $payload .= McpeBinary::writeString("");
        // multiplayerCorrelationId
        $payload .= McpeBinary::writeBool(false);
        // enableNewInventorySystem
        $payload .= McpeBinary::writeString("WatermossMC");
        // serverSoftwareVersion
        // Player Actor Properties (NBT)
        $payload .= McpeBinary::writeVarInt(0);
        // length of NBT bytes
        // Checksum & Template
        $payload .= McpeBinary::writeLLong(0);
        // blockPaletteChecksum
        $payload .= McpeBinary::writeUUID("00000000-0000-0000-0000-000000000000");
        // worldTemplateId
        $payload .= McpeBinary::writeBool(false);
        // enableClientSideChunkGeneration
        $payload .= McpeBinary::writeBool(false);
        // blockNetworkIdsAreHashes
        // Permissions & Logging
        $payload .= McpeBinary::writeBool(false);
        // networkPermissions (simplified bool)
        $payload .= McpeBinary::writeBool(false);
        // isLoggingChat
        // ServerJoinInformation
        $payload .= McpeBinary::writeBool(false);
        // ServerTelemetryData
        $payload .= McpeBinary::writeString("");
        // serverId
        $payload .= McpeBinary::writeString("");
        // scenarioId
        $payload .= McpeBinary::writeString("");
        // worldId
        $payload .= McpeBinary::writeString("");
        // ownerId
        self::sendBatch(ProtocolInfo::START_GAME_PACKET, $payload, $s, $sock);
    }
}
