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

namespace watermossmc\mcpe\protocol\clientbound;

use Socket;
use watermossmc\binary\Binary;
use watermossmc\binary\McpeBinary;
use watermossmc\mcpe\network\Session;
use watermossmc\mcpe\protocol\Packet;
use watermossmc\mcpe\protocol\ProtocolInfo;
use watermossmc\util\Logger;

final class ResourcePacksInfo extends Packet
{
    /**
     * @param array<int, array{uuid:string,version:string,size:int,key?:string}> $packs
     */
    public static function send(Session $s, Socket $sock, array $packs = [], bool $mustAccept = false): void
    {
        $p = Binary::writeBool($mustAccept);
        $p .= Binary::writeBool(false); // hasAddons
        $p .= Binary::writeBool(false); // hasScripts
        $p .= Binary::writeBool(false); // forceDisableVibrantVisuals

        $p .= Binary::writeUUID('00000000-0000-0000-0000-000000000000'); // worldTemplateId
        $p .= McpeBinary::writeString(""); // worldTemplateVersion

        $p .= McpeBinary::writeUnsignedVarInt(\count($packs));

        Logger::debug("[ResourcePacks] Raw payload hex: " . bin2hex($p));

        foreach ($packs as $pack) {
            $p .= Binary::writeUUID($pack['uuid']);
            $p .= McpeBinary::writeString($pack['version']);
            $p .= Binary::writeLLong($pack['size']); // Unsigned Long (8 bytes)
            $p .= McpeBinary::writeString($pack['key'] ?? "");
            $p .= McpeBinary::writeString(""); // subPackName
            $p .= McpeBinary::writeString(""); // contentId
            $p .= Binary::writeBool(false);    // hasScripts
            $p .= Binary::writeBool(false);    // isAddonPack
            $p .= Binary::writeBool(false);    // isRtxCapable
            $p .= McpeBinary::writeString(""); // cdnUrl
        }

        self::sendBatch(ProtocolInfo::RESOURCE_PACKS_INFO_PACKET, $p, $s, $sock);
    }
}
