<?php

declare(strict_types=1);

namespace WatermossMC\Minecraft\Packets;

use Ramsey\Uuid\Uuid;
use Socket;
use WatermossMC\Binary\Binary;
use WatermossMC\Network\Session;

final class PlayerList extends Packet
{
    public static function send(Session $s, Socket $sock): void
    {
        $uuid = $s->getUuid();

        $p = Binary::writeByte(0);
        $p .= Binary::writeVarInt(1);
        $p .= Binary::writeUUID($uuid);
        $p .= Binary::writeVarLong($s->getRuntimeId());
        $p .= Binary::writeString($s->getUsername());
        $p .= Binary::writeString("");
        $p .= Binary::writeString("");
        $p .= Binary::writeLInt(0); // build platform
        $p .= Binary::writeString(""); // skin id
        $p .= Binary::writeString(""); // playfab id
        $p .= Binary::writeString(""); // resource patch
        $p .= Binary::writeLInt(0); // skin image width
        $p .= Binary::writeLInt(0); // skin image height
        $p .= Binary::writeString(""); // skin image data
        $p .= Binary::writeVarInt(0); // animations
        $p .= Binary::writeLInt(0); // cape width
        $p .= Binary::writeLInt(0); // cape height
        $p .= Binary::writeString(""); // cape data
        $p .= Binary::writeString(""); // geometry data
        $p .= Binary::writeString(""); // geometry version
        $p .= Binary::writeString(""); // animation data
        $p .= Binary::writeString(""); // cape id
        $p .= Binary::writeString(""); // full skin id
        $p .= Binary::writeString(""); // arm size
        $p .= Binary::writeString(""); // skin color
        $p .= Binary::writeVarInt(0); // persona piece count
        $p .= Binary::writeVarInt(0); // persona piece tint count
        $p .= Binary::writeBool(false); // premium
        $p .= Binary::writeBool(false); // persona
        $p .= Binary::writeBool(false); // cape on classic
        $p .= Binary::writeBool(false); // primary user
        $p .= Binary::writeBool(false); // override
        $p .= Binary::writeBool(false); // skin verified

        self::sendBatch(ProtocolInfo::PLAYER_LIST_PACKET, $p, $s, $sock);
    }
}
