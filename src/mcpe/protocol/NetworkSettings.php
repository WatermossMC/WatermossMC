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

namespace watermossmc\mcpe\protocol;

use Socket;
use watermossmc\binary\McpeBinary;
use watermossmc\mcpe\network\Session;

final class NetworkSettings extends Packet
{
    public const COMPRESS_NOTHING = 0;
    public const COMPRESS_EVERYTHING = 1;

    public static function send(Session $s, Socket $sock): void
    {
        $p = McpeBinary::writeLShort(self::COMPRESS_EVERYTHING);
        $p .= McpeBinary::writeLShort(0);
        // 0 = Zlib, 1 = Snappy, 255 = None
        $p .= McpeBinary::writeBool(false);
        $p .= McpeBinary::writeByte(0);
        $p .= McpeBinary::writeFloat(0.0);
        $sendSeq = self::sendBatch(ProtocolInfo::NETWORK_SETTINGS_PACKET, $p, $s, $sock);
        $s->markNetworkSettingsReliableSeq($sendSeq);
    }
}
