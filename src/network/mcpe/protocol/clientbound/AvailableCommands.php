<?php

/*
 * __        __    _                                    __  __  ____
 * \ \      / /_ _| |_ ___ _ __ _ __ ___   ___  ___ ___|  \/  |/ ___|
 *  \ \ /\ / / _` | __/ _ \ '__| '_ ` _ \ / _ \/ __/ __| |\/| | |
 *   \ V  V / (_| | ||  __/ |  | | | | | | (_) \__ \__ \ |  | | |___
 *    \_/\_/ \__,_|\__\___|_|  |_| |_| |_|\___/|___/___/_|  |_|\____|
 *
 * WatermossMC
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author WatermossMC Team
 * @link https://github.com/watermossmc/WatermossMC
 */

declare(strict_types=1);

namespace watermossmc\network\mcpe\protocol\clientbound;

use Socket;
use watermossmc\binary\Binary;
use watermossmc\network\mcpe\protocol\Packet;
use watermossmc\network\mcpe\protocol\ProtocolInfo;
use watermossmc\network\Session;

final class AvailableCommands extends Packet
{
    public static function send(Session $s, Socket $sock): void
    {
        $payload = '';
        $payload .= Binary::writeVarInt(0);
        // enumValues[]
        $payload .= Binary::writeVarInt(0);
        // chainedSubCommandValues[]
        $payload .= Binary::writeVarInt(0);
        // postfixes[]
        $payload .= Binary::writeVarInt(0);
        // enums[]
        $payload .= Binary::writeVarInt(0);
        // chainedSubCommandData[]
        $payload .= Binary::writeVarInt(0);
        // commandData[]
        $payload .= Binary::writeVarInt(0);
        // softEnums[]
        $payload .= Binary::writeVarInt(0);
        // enumConstraints[]
        self::sendBatch(ProtocolInfo::AVAILABLE_COMMANDS_PACKET, $payload, $s, $sock);
    }
}
