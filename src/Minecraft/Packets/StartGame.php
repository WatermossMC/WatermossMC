<?php

declare(strict_types=1);

namespace WatermossMC\Minecraft\Packets;

use Socket;
use WatermossMC\Binary\Binary;
use WatermossMC\Binary\McpeBinary;
use WatermossMC\Minecraft\Data\BlockNames;
use WatermossMC\Minecraft\NBT\NBT;
use WatermossMC\Minecraft\Packets\Types\Experiments;
use WatermossMC\Network\Session;
use WatermossMC\Util\Config;
use WatermossMC\Util\Logger;

final class StartGame extends Packet
{
    public static function send(Session $s, Socket $sock): void
    {
        $worldName  = Config::getString('level_name', 'PHP World');
        $seed       = Config::getInt('level_seed', 12345);
        $gameModeId = 0;

        $position = $s->getPosition();
        $rotation = $s->getRotation();

        $blockPalette = BlockNames::getAll();

        $payload = '';

        // actorUniqueId
        $payload .= Binary::writeVarLong(1);
        // actorRuntimeId
        $payload .= Binary::writeVarLong($s->getRuntimeId());
        // playerGamemode
        $payload .= McpeBinary::writeSignedVarInt($gameModeId);

        // playerPosition
        $payload .= Binary::writeFloat($position['x']);
        $payload .= Binary::writeFloat($position['y']);
        $payload .= Binary::writeFloat($position['z']);

        // pitch, yaw (LE float)
        $payload .= Binary::writeFloat($rotation['pitch']);
        $payload .= Binary::writeFloat($rotation['yaw']);

        // ==================== LevelSettings ====================

        // seed
        $payload .= Binary::writeLLong($seed);

        // SpawnSettings: biomeType (LE short) + biome name (string) + dimension (signed varint)
        $payload .= Binary::writeLShort(0);          // biomeType = DEFAULT
        $payload .= McpeBinary::writeString('');     // biome name
        $payload .= McpeBinary::writeSignedVarInt(0);// dimension = overworld

        // generator (signed varint): 1 = infinite
        $payload .= McpeBinary::writeSignedVarInt(1);
        // worldGamemode
        $payload .= McpeBinary::writeSignedVarInt($gameModeId);
        // hardcore
        $payload .= Binary::writeBool(false);
        // difficulty
        $payload .= McpeBinary::writeSignedVarInt(1);

        // spawnPosition: BlockPosition (signed varint x, unsigned varint y, signed varint z)
        $payload .= McpeBinary::writeSignedVarInt(0);  // x
        $payload .= Binary::writeVarInt(64);           // y (unsigned)
        $payload .= McpeBinary::writeSignedVarInt(0);  // z

        $payload .= Binary::writeBool(true);           // hasAchievementsDisabled
        $payload .= McpeBinary::writeSignedVarInt(0);  // editorWorldType = NON_EDITOR
        $payload .= Binary::writeBool(false);          // createdInEditorMode
        $payload .= Binary::writeBool(false);          // exportedFromEditorMode
        $payload .= McpeBinary::writeSignedVarInt(0);  // time
        $payload .= McpeBinary::writeSignedVarInt(0);  // eduEditionOffer = NONE
        $payload .= Binary::writeBool(false);          // hasEduFeaturesEnabled
        $payload .= McpeBinary::writeString('');       // eduProductUUID
        $payload .= Binary::writeFloat(0.0);           // rainLevel
        $payload .= Binary::writeFloat(0.0);           // lightningLevel
        $payload .= Binary::writeBool(false);          // hasConfirmedPlatformLockedContent
        $payload .= Binary::writeBool(true);           // isMultiplayerGame
        $payload .= Binary::writeBool(true);           // hasLANBroadcast
        $payload .= McpeBinary::writeSignedVarInt(2);  // xboxLiveBroadcastMode = PUBLIC
        $payload .= McpeBinary::writeSignedVarInt(2);  // platformBroadcastMode = PUBLIC
        $payload .= Binary::writeBool(true);           // commandsEnabled
        $payload .= Binary::writeBool(false);          // isTexturePacksRequired

        // gameRules (count = 0)
        $payload .= Binary::writeVarInt(0);

        // Experiments
        $payload .= Experiments::writeEmpty();

        $payload .= Binary::writeBool(false);          // hasBonusChestEnabled
        $payload .= Binary::writeBool(false);          // hasStartWithMapEnabled
        $payload .= McpeBinary::writeSignedVarInt(1);  // defaultPlayerPermission = MEMBER
        $payload .= Binary::writeLInt(8);              // serverChunkTickRadius (LE signed int)
        $payload .= Binary::writeBool(false);          // hasLockedBehaviorPack
        $payload .= Binary::writeBool(false);          // hasLockedResourcePack
        $payload .= Binary::writeBool(false);          // isFromLockedWorldTemplate
        $payload .= Binary::writeBool(false);          // useMsaGamertagsOnly
        $payload .= Binary::writeBool(false);          // isFromWorldTemplate
        $payload .= Binary::writeBool(false);          // isWorldTemplateOptionLocked
        $payload .= Binary::writeBool(false);          // onlySpawnV1Villagers
        $payload .= Binary::writeBool(false);          // disablePersona
        $payload .= Binary::writeBool(false);          // disableCustomSkins
        $payload .= Binary::writeBool(false);          // muteEmoteAnnouncements
        $payload .= McpeBinary::writeString(ProtocolInfo::MINECRAFT_VERSION_NETWORK); // vanillaVersion
        $payload .= Binary::writeLInt(0);              // limitedWorldWidth (LE signed int)
        $payload .= Binary::writeLInt(0);              // limitedWorldLength (LE signed int)
        $payload .= Binary::writeBool(true);           // isNewNether

        // eduSharedUriResource: EducationUriResource (always written, empty strings = disabled)
        $payload .= McpeBinary::writeString('');       // buttonName
        $payload .= McpeBinary::writeString('');       // linkUri

        // experimentalGameplayOverride (optional bool): false = absent
        $payload .= Binary::writeBool(false);

        $payload .= Binary::writeUInt8(0);             // chatRestrictionLevel = NONE
        $payload .= Binary::writeBool(false);          // disablePlayerInteractions
        $payload .= McpeBinary::writeSignedVarInt(0);  // serverEditorConnectionPolicy
        $payload .= Binary::writeBool(false);          // allowAnonymousBlockDropsInEditorWorlds

        // ==================== end LevelSettings ====================

        $payload .= McpeBinary::writeString('');           // levelId
        $payload .= McpeBinary::writeString($worldName);   // worldName
        $payload .= McpeBinary::writeString('');           // premiumWorldTemplateId
        $payload .= Binary::writeBool(false);              // isTrial

        // PlayerMovementSettings
        $payload .= McpeBinary::writeSignedVarInt(0);      // rewindHistorySize
        $payload .= Binary::writeBool(false);              // serverAuthoritativeBlockBreaking

        $payload .= Binary::writeLLong(0);                 // currentTick (LE uint64)
        $payload .= McpeBinary::writeSignedVarInt(0);      // enchantmentSeed

        $payload .= Binary::writeVarInt(count($blockPalette));

        foreach ($blockPalette as $blockName) {
            $payload .= McpeBinary::writeString($blockName);
            // Sementara kita kirim NBT Compound kosong. 
            // Jika klien 1.20+ menolak ini, kita harus upgrade ke canonical_block_states.nbt nanti.
            $payload .= NBT::compound([]); 
        }

        $payload .= McpeBinary::writeString('');           // multiplayerCorrelationId
        $payload .= Binary::writeBool(false);              // enableNewInventorySystem
        $payload .= McpeBinary::writeString('WatermossMC');// serverSoftwareVersion
        $payload .= NBT::compound([]);                     // playerActorProperties (NBT compound)
        $payload .= Binary::writeLLong(0);                 // blockPaletteChecksum (LE uint64)
        $payload .= Binary::writeUUID('00000000-0000-0000-0000-000000000000'); // worldTemplateId
        $payload .= Binary::writeBool(false);              // enableClientSideChunkGeneration
        $payload .= Binary::writeBool(false);              // blockNetworkIdsAreHashes

        // NetworkPermissions
        $payload .= Binary::writeBool(false);              // serverAuthSounds

        // isLoggingChat
        $payload .= Binary::writeBool(false);

        // ServerJoinInformation
        $payload .= Binary::writeBool(false);

        // ServerTelemetryData
        $payload .= McpeBinary::writeString(""); // serverId
        $payload .= McpeBinary::writeString(""); // scenarioId
        $payload .= McpeBinary::writeString(""); // worldId
        $payload .= McpeBinary::writeString(""); // ownerId

      $hex = bin2hex($payload);
Logger::debug("StartGame payload hex: " . $hex);


        self::sendBatch(ProtocolInfo::START_GAME_PACKET, $payload, $s, $sock);
    }
}