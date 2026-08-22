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

namespace watermossmc\mcpe\protocol\clientbound;

use Socket;
use watermossmc\binary\Binary;
use watermossmc\mcpe\network\Session;
use watermossmc\mcpe\protocol\Packet;
use watermossmc\mcpe\protocol\ProtocolInfo;
use watermossmc\player\Player;

final class UpdatePosition extends Packet
{
    public static function send(Player $p, Session $s, Socket $sock): void
    {
        $payload = Binary::writeLong(1);
        $payload .= pack("g", $p->x);
        $payload .= pack("g", $p->y);
        $payload .= pack("g", $p->z);
        $payload .= pack("g", $p->pitch);
        $payload .= pack("g", $p->yaw);
        $payload .= pack("g", 0.0);
        $payload .= Binary::writeBool($p->onGround);
        self::sendBatch(ProtocolInfo::MOVE_PLAYER_PACKET, $payload, $s, $sock);
    }
}
