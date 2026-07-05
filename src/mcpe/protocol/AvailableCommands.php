<?php

declare(strict_types=1);

namespace watermossmc\mcpe\protocol;

use Socket;
use watermossmc\binary\Binary;
use watermossmc\mcpe\network\Session;

final class AvailableCommands extends Packet
{
    public static function send(Session $s, Socket $sock): void
    {
        $payload = '';

        $payload .= Binary::writeVarInt(0); // enumValues[]
        $payload .= Binary::writeVarInt(0); // chainedSubCommandValues[]
        $payload .= Binary::writeVarInt(0); // postfixes[]
        $payload .= Binary::writeVarInt(0); // enums[]
        $payload .= Binary::writeVarInt(0); // chainedSubCommandData[]
        $payload .= Binary::writeVarInt(0); // commandData[]
        $payload .= Binary::writeVarInt(0); // softEnums[]
        $payload .= Binary::writeVarInt(0); // enumConstraints[]

        self::sendBatch(ProtocolInfo::AVAILABLE_COMMANDS_PACKET, $payload, $s, $sock);
    }
}
