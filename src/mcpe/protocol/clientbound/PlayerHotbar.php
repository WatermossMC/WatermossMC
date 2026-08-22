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

declare (strict_types=1);

namespace watermossmc\mcpe\protocol\clientbound;

use Socket;
use watermossmc\binary\Binary;
use watermossmc\mcpe\network\Session;
use watermossmc\mcpe\protocol\Packet;
use watermossmc\mcpe\protocol\ProtocolInfo;

final class PlayerHotbar extends Packet
{
    public const WINDOW_INVENTORY = 0;

    public static function send(Session $s, Socket $sock, int $selectedSlot = 0, int $windowId = self::WINDOW_INVENTORY, bool $selectHotbarSlot = true): void
    {
        $payload = '';
        $payload .= Binary::writeVarInt($selectedSlot);
        // selectedHotbarSlot (varuint)
        $payload .= Binary::writeUInt8($windowId);
        // windowId (unsigned byte)
        $payload .= Binary::writeBool($selectHotbarSlot);
        // selectHotbarSlot
        self::sendBatch(ProtocolInfo::PLAYER_HOTBAR_PACKET, $payload, $s, $sock);
    }
}
