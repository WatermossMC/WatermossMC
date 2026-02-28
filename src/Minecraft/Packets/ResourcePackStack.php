<?php

declare(strict_types=1);

namespace WatermossMC\Minecraft\Packets;

use Socket;
use WatermossMC\Binary\Binary;
use WatermossMC\Minecraft\Packets\Types\Experiments;
use WatermossMC\Network\Session;

final class ResourcePackStack extends Packet
{
    public static function send(
        Session $s, 
        Socket $sock, 
        array $resourcePacks = [], 
        array $behaviorPacks = [], 
        bool $mustAccept = false
    ): void {
        $p = Binary::writeBool($mustAccept);

        // Behavior Packs
        $p .= Binary::writeVarInt(count($behaviorPacks));
        foreach ($behaviorPacks as $pack) {
            $p .= Binary::writeStringInt($pack['uuid']);
            $p .= Binary::writeStringInt($pack['version']);
            $p .= Binary::writeStringInt(""); // subPackName
        }

        // Resource Packs
        $p .= Binary::writeVarInt(count($resourcePacks));
        foreach ($resourcePacks as $pack) {
            $p .= Binary::writeStringInt($pack['uuid']);
            $p .= Binary::writeStringInt($pack['version']);
            $p .= Binary::writeStringInt(""); // subPackName
        }

        $p .= Binary::writeStringInt("1.21.124");
        $p .= Experiments::writeEmpty();
        $p .= Binary::writeBool(false); // useVanillaEditorPacks

        self::sendBatch(0x07, $p, $s, $sock);
    }
}
