<?php

<<<<<<< HEAD
=======
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

>>>>>>> 866a1c0 (...)
declare(strict_types=1);

namespace watermossmc\mcpe\protocol;

use Socket;
use watermossmc\binary\Binary;
use watermossmc\binary\McpeBinary;
use watermossmc\mcpe\network\Session;

final class PlayerList extends Packet
{
    public const TYPE_ADD = 0;
    public const TYPE_REMOVE = 1;

    public static function sendAdd(Session $s, Socket $sock): void
    {
        $uuid = $s->getUuid();
        $name = $s->getPlayerName();
        $xuid = $s->getXuid();
        $rId = $s->getRuntimeId();

        $payload = '';
        $payload .= Binary::writeUInt8(self::TYPE_ADD); // type
        $payload .= Binary::writeVarInt(1);             // entry count

        // --- Entry ---
        $payload .= self::writeUuidBytes($uuid);        // UUID (16 bytes LE)
        $payload .= Binary::writeVarLong($rId);         // actorUniqueId
        $payload .= McpeBinary::writeString($name);     // username
        $payload .= McpeBinary::writeString((string) $xuid);     // xboxUserId
        $payload .= McpeBinary::writeString('');        // platformChatId
        $payload .= Binary::writeLInt(-1);              // buildPlatform (-1 = unknown)
        $payload .= self::writeMinimalSkin($name);      // skinData
        $payload .= Binary::writeBool(false);           // isTeacher
        $payload .= Binary::writeBool(false);           // isHost
        $payload .= Binary::writeBool(false);           // isSubClient
        $payload .= Binary::writeLInt(0xFFFFFFFF);      // color (ARGB white)

        // skinVerified flags (one per entry)
        $payload .= Binary::writeBool(false);

        self::sendBatch(ProtocolInfo::PLAYER_LIST_PACKET, $payload, $s, $sock);
    }

    public static function sendRemove(Session $s, Socket $sock): void
    {
        $payload = '';
        $payload .= Binary::writeUInt8(self::TYPE_REMOVE);
        $payload .= Binary::writeVarInt(1);
        $payload .= self::writeUuidBytes($s->getUuid());

        self::sendBatch(ProtocolInfo::PLAYER_LIST_PACKET, $payload, $s, $sock);
    }

    /**
     * Writes a UUID string (e.g. "xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx") as 16 LE bytes.
     */
    private static function writeUuidBytes(string $uuid): string
    {
        $hex = str_replace('-', '', $uuid);
        if (\strlen($hex) !== 32) {
            $hex = str_pad($hex, 32, '0');
        }
        return pack('H*', $hex);
    }

    /**
     * Writes a minimal valid skin payload.
     * Bedrock requires: skinId, playFabId, skinResourcePatch, skinData (width/height/data),
     * capes, geometry, animationData, various flags, and persona fields.
     */
    private static function writeMinimalSkin(string $playerName): string
    {
        // 64x64 RGBA skin (all black, 16384 bytes)
        $skinWidth = 64;
        $skinHeight = 64;
        $skinPixels = str_repeat("\x00\x00\x00\xff", $skinWidth * $skinHeight);

        $s = '';
        $s .= McpeBinary::writeString($playerName . '_skin'); // skinId
        $s .= McpeBinary::writeString('');                    // playFabId
        $s .= McpeBinary::writeString(self::defaultResourcePatch()); // skinResourcePatch

        // skin image
        $s .= Binary::writeLInt($skinWidth);
        $s .= Binary::writeLInt($skinHeight);
        $s .= Binary::writeLInt(\strlen($skinPixels));
        $s .= $skinPixels;

        // animations count
        $s .= Binary::writeLInt(0);

        // cape image (empty)
        $s .= Binary::writeLInt(0); // width
        $s .= Binary::writeLInt(0); // height
        $s .= Binary::writeLInt(0); // data length

        // geometry data
        $s .= McpeBinary::writeString('');
        // geometry data engine version
        $s .= McpeBinary::writeString('1.12.0');

        // animation data
        $s .= McpeBinary::writeString('');

        // capeId
        $s .= McpeBinary::writeString('');

        // fullSkinId
        $s .= McpeBinary::writeString($playerName . '_skin_full');

        // armSize
        $s .= McpeBinary::writeString('wide');

        // skinColor
        $s .= McpeBinary::writeString('#0');

        // persona pieces count
        $s .= Binary::writeLInt(0);

        // persona piece tint colors count
        $s .= Binary::writeLInt(0);

        // isPremiumSkin
        $s .= Binary::writeBool(false);

        // isPersonaSkin
        $s .= Binary::writeBool(false);

        // isPersonaCapeOnClassicSkin
        $s .= Binary::writeBool(false);

        // isPrimaryUser
        $s .= Binary::writeBool(true);

        // isOverride (trusted skin)
        $s .= Binary::writeBool(false);

        return $s;
    }

    private static function defaultResourcePatch(): string
    {
        $res = json_encode([
            'geometry' => ['default' => 'geometry.humanoid.custom'],
        ]);
        if ($res === false) {
            return '{}';
        }
        return $res;
    }
}
