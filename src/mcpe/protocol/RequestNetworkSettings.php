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
use watermossmc\binary\Binary;
use watermossmc\mcpe\network\RakNet;
use watermossmc\mcpe\network\Session;
use watermossmc\util\Logger;

final class RequestNetworkSettings extends Packet
{
    public static function read(string $payload, int &$o, Session $s, Socket $sock): bool
    {
        $protocol = Binary::readInt($payload, $o);
        if ($protocol !== ProtocolInfo::CURRENT_PROTOCOL) {
            Logger::error("[RequestNetworkSettings] Protocol mismatch. Client: {$protocol}, Server: " . ProtocolInfo::CURRENT_PROTOCOL);
            PlayStatus::sendFailedClient($s, $sock);
            $msg = $protocol < ProtocolInfo::CURRENT_PROTOCOL ? "Outdated client" : "Outdated server";
            Disconnect::send($s, $sock, $msg);
            RakNet::flush($s, $sock);
            return false;
        }
        Logger::debug("[RequestNetworkSettings] Protocol version verified: {$protocol}");
        return true;
    }
}
