<?php

declare(strict_types=1);

namespace WatermossMC\Minecraft\Packets;

use Socket;
use WatermossMC\Binary\Binary;
use WatermossMC\Binary\McpeBinary;
use WatermossMC\Network\Session;
use WatermossMC\Util\Logger;

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

        $p .= Binary::writeLShort(count($packs));

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
