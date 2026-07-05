<?php

declare(strict_types=1);

namespace watermossmc\mcpe\protocol;

use Socket;
use watermossmc\binary\Binary;
use watermossmc\mcpe\network\Session;

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

        $p .= Binary::writeFloat(0.0);
        $p .= Binary::writeFloat(0.0);
        $p .= Binary::writeFloat(0.0);

        $p .= Binary::writeFloat($rot['pitch']);
        $p .= Binary::writeFloat($rot['yaw']);
        $p .= Binary::writeFloat($rot['yaw']);

        $p .= Binary::writeVarInt(0); // air item stack wrapper
        $p .= Binary::writeVarInt($s->getGameMode());

        $p .= Binary::writeVarInt(0); // metadata count
        $p .= Binary::writeVarInt(0); // synced int properties
        $p .= Binary::writeVarInt(0); // synced float properties

        $p .= Binary::writeLong(0); // target actor unique id for abilities
        $p .= Binary::writeByte(1); // player permission
        $p .= Binary::writeByte(0); // command permission
        $p .= Binary::writeByte(0); // ability layer count
        $p .= Binary::writeVarInt(0); // link count
        $p .= Binary::writeString(""); // device id
        $p .= Binary::writeLInt(0); // build platform
        self::sendBatch(ProtocolInfo::ADD_PLAYER_PACKET, $p, $s, $sock);
    }
}
