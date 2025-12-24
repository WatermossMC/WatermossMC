<?php

declare(strict_types=1);

namespace WatermossMC\Minecraft\Packets;

use Socket;
use WatermossMC\Binary\Binary;
use WatermossMC\Network\Session;

final class ResourcePacksInfo extends Packet
{
    public static function send(Session $s, Socket $sock): void
    {
        $p = Binary::writeByte(0x06);

        // mustAccept
        $p .= Binary::writeBool(false);

        // hasAddons
        $p .= Binary::writeBool(false);

        // hasScripts
        $p .= Binary::writeBool(false);

        // forceDisableVibrantVisuals
        $p .= Binary::writeBool(false);

        // worldTemplateId
        $p .= Binary::writeUUID('00000000-0000-0000-0000-000000000000');

        // worldTemplateVersion
        $p .= Binary::writeStringInt("");

        // resourcePackCount
        $p .= Binary::writeShort(0);

        self::sendBatch($p, $s, $sock);
    }
}
