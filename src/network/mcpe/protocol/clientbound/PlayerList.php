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
use watermossmc\network\Session;
use watermossmc\player\Player;

final class PlayerList extends Packet
{
    public const TYPE_REMOVE = 0;
    public const TYPE_ADD = 1;
    private const LEGACY_TYPE_ADD = 0;
    private const LEGACY_TYPE_REMOVE = 1;
    private const DEVICE_OS_UNKNOWN = -1;
    private const ARM_SIZE_WIDE = 1;
    private const EMPTY_GEOMETRY_DATA = '{}';
    private const TRUSTED_SKIN_FLAG_UNSET = 'unset';

    /**
     * Adds every player in $players to the tab list of the client behind $s / $sock.
     *
     * @param Player[] $players
     */
    public static function sendAdd(Session $s, Socket $sock, array $players): void
    {
        if ($players === []) {
            return;
        }

        $payload = McpeBinary::writeUnsignedVarInt(\count($players));
        foreach ($players as $player) {
            $payload .= McpeBinary::writeUnsignedVarInt(self::TYPE_ADD);
            $payload .= McpeBinary::writeByte(self::LEGACY_TYPE_ADD);

            $name = $player->getUsername();
            $payload .= McpeBinary::writeUUID($player->getUuid());
            $payload .= McpeBinary::writeSignedVarLong($player->getRuntimeId());
            $payload .= McpeBinary::writeString($name);
            $payload .= McpeBinary::writeString((string) $player->session->getXuid());
            $payload .= McpeBinary::writeString('');
            $payload .= McpeBinary::writeLInt(self::DEVICE_OS_UNKNOWN);
            $payload .= self::writeMinimalSkin($name);
            $payload .= McpeBinary::writeBool(false);
            $payload .= McpeBinary::writeBool(false);
            $payload .= McpeBinary::writeBool(false);
            $payload .= McpeBinary::writeLInt(0xFFFFFFFF);
        }

        self::sendBatch(ProtocolInfo::PLAYER_LIST_PACKET, $payload, $s, $sock);
    }

    /**
     * Removes a player from the tab list of the client behind $s / $sock.
     *
     * @param string|null $uuid UUID of the player to remove. Defaults to the
     *                          session's own UUID (previous behaviour).
     */
    public static function sendRemove(Session $s, Socket $sock, ?string $uuid = null): void
    {
        $payload = McpeBinary::writeUnsignedVarInt(1);
        $payload .= McpeBinary::writeUnsignedVarInt(self::TYPE_REMOVE);
        $payload .= McpeBinary::writeByte(self::LEGACY_TYPE_REMOVE);
        $payload .= McpeBinary::writeUUID($uuid ?? $s->getUuid());
        self::sendBatch(ProtocolInfo::PLAYER_LIST_PACKET, $payload, $s, $sock);
    }

    private static function writeMinimalSkin(string $playerName): string
    {
        $width = 64;
        $height = 64;
        $pixels = str_repeat("\x00\x00\x00\xff", $width * $height);

        $s = McpeBinary::writeString($playerName . '_skin');
        $s .= McpeBinary::writeString('');
        $s .= McpeBinary::writeString(self::defaultResourcePatch());
        $s .= self::writeSkinImage($width, $height, $pixels);
        $s .= McpeBinary::writeUnsignedVarInt(0);
        $s .= self::writeSkinImage(0, 0, '');
        $s .= McpeBinary::writeString(self::EMPTY_GEOMETRY_DATA);
        $s .= McpeBinary::writeString(ProtocolInfo::MINECRAFT_VERSION_NETWORK);
        $s .= McpeBinary::writeString('');
        $s .= McpeBinary::writeString('');
        $s .= McpeBinary::writeString($playerName . '_skin_full');
        $s .= McpeBinary::writeByte(self::ARM_SIZE_WIDE);
        $s .= McpeBinary::writeLInt(0);
        $s .= McpeBinary::writeUnsignedVarInt(0);
        $s .= McpeBinary::writeUnsignedVarInt(0);
        $s .= McpeBinary::writeBool(false);
        $s .= McpeBinary::writeBool(false);
        $s .= McpeBinary::writeBool(false);
        $s .= McpeBinary::writeBool(true);
        $s .= McpeBinary::writeBool(true);
        $s .= McpeBinary::writeString(self::TRUSTED_SKIN_FLAG_UNSET);
        $s .= McpeBinary::writeString('');
        return $s;
    }

    private static function writeSkinImage(int $width, int $height, string $data): string
    {
        return McpeBinary::writeLInt($width)
            . McpeBinary::writeLInt($height)
            . McpeBinary::writeString($data);
    }

    private static function defaultResourcePatch(): string
    {
        $res = json_encode(['geometry' => ['default' => 'geometry.humanoid.custom']]);
        return $res === false ? '{}' : $res;
    }
}
