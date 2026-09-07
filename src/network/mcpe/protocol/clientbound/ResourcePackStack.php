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
use watermossmc\binary\Binary;
use watermossmc\binary\McpeBinary;
use watermossmc\network\mcpe\protocol\Packet;
use watermossmc\network\mcpe\protocol\ProtocolInfo;
use watermossmc\network\mcpe\protocol\types\Experiments;
use watermossmc\network\Session;

final class ResourcePackStack extends Packet
{
    /**
     * @param array<int, array{uuid:string,version:string,subPackName?:string}> $resourcePacks
     */
    public static function send(Session $s, Socket $sock, array $resourcePacks = [], bool $mustAccept = false, string $baseGameVersion = ProtocolInfo::MINECRAFT_VERSION_NETWORK, bool $useVanillaEditorPacks = false): void
    {
        $p = Binary::writeBool($mustAccept);
        // Resource packs
        $p .= McpeBinary::writeVarInt(\count($resourcePacks));
        foreach ($resourcePacks as $pack) {
            $p .= McpeBinary::writeString($pack['uuid']);
            $p .= McpeBinary::writeString($pack['version']);
            $p .= McpeBinary::writeString($pack['subPackName'] ?? "");
        }
        $p .= McpeBinary::writeString($baseGameVersion);
        $p .= Experiments::writeEmpty();
        $p .= Binary::writeBool($useVanillaEditorPacks);
        self::sendBatch(ProtocolInfo::RESOURCE_PACK_STACK_PACKET, $p, $s, $sock);
    }
}
