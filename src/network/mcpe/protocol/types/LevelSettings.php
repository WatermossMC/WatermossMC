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

namespace watermossmc\network\mcpe\protocol\types;

use watermossmc\binary\McpeBinary;

final class LevelSettings
{
    public static function write(): string
    {
        $payload .= McpeBinary::writeSignedVarInt(0); // editorWorldType = NON_EDITOR
        $payload .= McpeBinary::writeBool(false); // createdInEditorMode
        $payload .= McpeBinary::writeBool(false); // exportedFromEditorMode
        $payload .= McpeBinary::writeSignedVarInt(-1); // time
        $payload .= McpeBinary::writeUnsignedVarInt(0); // eduEditionOffer
        $payload .= McpeBinary::writeBool(false); // hasEduFeaturesEnabled
        $payload .= McpeBinary::writeString(''); // eduProductUUID
        $payload .= McpeBinary::writeFloat(0.0); // rainLevel
        $payload .= McpeBinary::writeFloat(0.0); // lightningLevel
        $payload .= McpeBinary::writeBool(false); // hasConfirmedPlatformLockedContent
        $payload .= McpeBinary::writeBool(true); // isMultiplayerGame
        $payload .= McpeBinary::writeBool(true); // hasLANBroadcast
        $payload .= McpeBinary::writeSignedVarInt(0); // xboxLiveBroadcastMode (0 = Public)
        $payload .= McpeBinary::writeSignedVarInt(0); // platformBroadcastMode (0 = Public)
        $payload .= McpeBinary::writeBool(true); // commandsEnabled
        $payload .= McpeBinary::writeBool(false); // isTexturePacksRequired

        // GameRules
        $payload .= McpeBinary::writeUnsignedVarInt(0);

        // Experiments
        $payload .= McpeBinary::writeLInt(0); // experiments count
        $payload .= McpeBinary::writeBool(false); // hasPreviouslyUsedExperiments

        $payload .= McpeBinary::writeBool(false); // hasBonusChestEnabled
        $payload .= McpeBinary::writeBool(false); // hasStartWithMapEnabled

        $payload .= McpeBinary::writeByte(1); // defaultPlayerPermission (1 = Member)
        $payload .= McpeBinary::writeLInt(4); // serverChunkTickRadius

        $payload .= McpeBinary::writeBool(false); // hasLockedBehaviorPack
        $payload .= McpeBinary::writeBool(false); // hasLockedResourcePack
        $payload .= McpeBinary::writeBool(false); // isFromLockedWorldTemplate
        $payload .= McpeBinary::writeBool(false); // useMsaGamertagsOnly
        $payload .= McpeBinary::writeBool(false); // isFromWorldTemplate
        $payload .= McpeBinary::writeBool(false); // isWorldTemplateOptionLocked
        $payload .= McpeBinary::writeBool(false); // onlySpawnV1Villagers
        $payload .= McpeBinary::writeBool(false); // disablePersona
        $payload .= McpeBinary::writeBool(false); // disableCustomSkins
        $payload .= McpeBinary::writeBool(false); // muteEmoteAnnouncements
        $payload .= McpeBinary::writeString(ProtocolInfo::MINECRAFT_VERSION_NETWORK); // vanillaVersion
        $payload .= McpeBinary::writeLInt(0); // limitedWorldWidth
        $payload .= McpeBinary::writeLInt(0); // limitedWorldLength
        $payload .= McpeBinary::writeBool(true); // isNewNether

        // EduSharedUriResource
        $payload .= McpeBinary::writeString(""); // buttonName
        $payload .= McpeBinary::writeString(""); // linkUri

        // experimentalGameplayOverride
        $payload .= McpeBinary::writeBool(false); // hasValue = false

        $payload .= McpeBinary::writeByte(0); // chatRestrictionLevel
        $payload .= McpeBinary::writeBool(false); // disablePlayerInteractions
        $payload .= McpeBinary::writeSignedVarInt(0); // serverEditorConnectionPolicy
        $payload .= McpeBinary::writeBool(false); // allowAnonymousBlockDropsInEditorWorlds

        return $payload;
    }
}
