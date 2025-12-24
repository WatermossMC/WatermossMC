<?php

declare(strict_types=1);

namespace WatermossMC\Minecraft\Packets;

use Socket;
use WatermossMC\Binary\Binary;
use WatermossMC\Network\Session;

final class UpdateAbilities extends Packet
{
    public static function send(Session $s, Socket $sock): void
    {
        $p = Binary::writeByte(ProtocolInfo::UPDATE_ABILITIES_PACKET);
        $p .= Binary::writeVarLong($s->getRuntimeId());
        $p .= Binary::writeByte(0); // player permissions
        $p .= Binary::writeByte(0); // command permissions
        $p .= Binary::writeVarInt(0); // abilities count

        self::sendBatch($p, $s, $sock);
    }
}
