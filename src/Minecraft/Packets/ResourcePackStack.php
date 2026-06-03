<?php

declare(strict_types=1);

namespace WatermossMC\Minecraft\Packets;

use Socket;
use WatermossMC\Binary\Binary;
use WatermossMC\Minecraft\Packets\Types\Experiments;
use WatermossMC\Network\Session;

final class ResourcePackStack extends Packet
{
    /**
     * @param array<int, array{uuid:string,version:string}> $resourcePacks
     * @param array<int, array{uuid:string,version:string}> $behaviorPacks
     */
    public static function send(
        Session $s, 
        Socket $sock, 
        array $resourcePacks = [], 
        array $behaviorPacks = [], 
        bool $mustAccept = false
    ): void {
        $p = Binary::writeBool($mustAccept);

        $p .= Binary::writeVarInt(count($behaviorPacks));
        foreach ($behaviorPacks as $pack) {
            $p .= Binary::writeString($pack['uuid']);
            $p .= Binary::writeString($pack['version']);
            $p .= Binary::writeString(""); // subPackName
        }

        $p .= Binary::writeVarInt(count($resourcePacks));
        foreach ($resourcePacks as $pack) {
            $p .= Binary::writeString($pack['uuid']);
            $p .= Binary::writeString($pack['version']);
            $p .= Binary::writeString(""); // subPackName
        }

        $p .= Binary::writeString("1.21.124");
        $p .= Experiments::writeEmpty();
        $p .= Binary::writeBool(false); // useVanillaEditorPacks

        self::sendBatch(ProtocolInfo::RESOURCE_PACK_STACK_PACKET, $p, $s, $sock);
    }
}
