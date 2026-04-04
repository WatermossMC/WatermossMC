<?php

declare(strict_types=1);

namespace WatermossMC\Minecraft\Packets;

use Socket;
use WatermossMC\Binary\Binary;
use WatermossMC\Network\Session;

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
        $p .= Binary::writeStringInt(""); // worldTemplateVersion

        $p .= Binary::writeShort(count($packs));
        
        foreach ($packs as $pack) {
            $p .= Binary::writeUUID($pack['uuid']);
            $p .= Binary::writeStringInt($pack['version']);
            $p .= Binary::writeLong($pack['size']); // Unsigned Long (8 bytes)
            $p .= Binary::writeStringInt($pack['key'] ?? "");
            $p .= Binary::writeStringInt(""); // subPackName
            $p .= Binary::writeStringInt(""); // contentId
            $p .= Binary::writeBool(false);    // hasScripts
            $p .= Binary::writeBool(false);    // isAddonPack
            $p .= Binary::writeBool(false);    // isRtxCapable
            $p .= Binary::writeStringInt(""); // cdnUrl
        }

        self::sendBatch(0x06, $p, $s, $sock);
    }
}
