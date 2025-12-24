<?php

declare(strict_types=1);

namespace WatermossMC\Minecraft\Packets;

use Socket;
use WatermossMC\Binary\Binary;
use WatermossMC\Network\Session;

final class AddPlayer extends Packet
{
    public static function send(Session $s, Socket $sock): void
    {
        $pos = $s->getPosition();
        $rot = $s->getRotation();

        $p = Binary::writeByte(0x0c);
        $p .= Binary::writeUUID($s->getUuid());
        $p .= Binary::writeString($s->getUsername());
        $p .= Binary::writeVarLong($s->getRuntimeId());
        $p .= Binary::writeString("");

        $p .= Binary::writeFloat($pos['x']);
        $p .= Binary::writeFloat($pos['y']);
        $p .= Binary::writeFloat($pos['z']);

        $p .= Binary::writeBool(false); // motion = null

        $p .= Binary::writeFloat($rot['pitch']);
        $p .= Binary::writeFloat($rot['yaw']);
        $p .= Binary::writeFloat($rot['yaw']);

        $p .= Binary::itemStackAir();
        $p .= Binary::writeVarInt($s->getGameMode());

        $p .= Binary::writeVarInt(0); // metadata
        $p .= Binary::writeVarInt(0); // property sync
        $p .= Binary::writeVarInt(0);

        $p .= Binary::writeVarInt(0); // links
        $p .= Binary::writeString("");
        $p .= Binary::writeInt(0);
        self::sendBatch($p, $s, $sock);
    }
}
