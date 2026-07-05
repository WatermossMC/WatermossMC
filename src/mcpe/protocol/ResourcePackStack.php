<?php

declare(strict_types=1);

namespace watermossmc\mcpe\protocol;

use Socket;
use watermossmc\binary\Binary;
use watermossmc\binary\McpeBinary;
use watermossmc\mcpe\network\Session;
use watermossmc\mcpe\protocol\types\Experiments;

final class ResourcePackStack extends Packet
{
    /**
     * @param array<int, array{uuid:string,version:string,subPackName?:string}> $resourcePacks
     */
    public static function send(
        Session $s,
        Socket $sock,
        array $resourcePacks = [],
        bool $mustAccept = false,
        string $baseGameVersion = ProtocolInfo::MINECRAFT_VERSION_NETWORK,
        bool $useVanillaEditorPacks = false
    ): void {
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
