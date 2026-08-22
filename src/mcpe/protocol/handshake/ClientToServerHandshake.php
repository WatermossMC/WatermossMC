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

namespace watermossmc\mcpe\protocol\handshake;

use watermossmc\mcpe\protocol\Packet;
use watermossmc\util\Logger;

final class ClientToServerHandshake extends Packet
{
    public static function read(string $buf, int &$o): void
    {
        $length = \strlen($buf) - $o;
        Logger::debug("[0x04] ClientToServerHandshake payload length={$length}");
        if ($length <= 0) {
            return;
        }
        // Consume any remaining handshake payload without failing the connection.
        // The packet format may vary between protocol versions, so read any remaining bytes safely.
        $remaining = \strlen($buf) - $o;
        if ($remaining > 0) {
            $o += $remaining;
        }
    }
}
