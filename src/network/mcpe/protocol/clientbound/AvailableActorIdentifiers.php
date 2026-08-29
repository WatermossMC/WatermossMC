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
use watermossmc\network\mcpe\cache\StaticPacketCache;
use watermossmc\network\Session;
use watermossmc\network\mcpe\protocol\Packet;
use watermossmc\network\mcpe\protocol\ProtocolInfo;
use watermossmc\util\Logger;

final class AvailableActorIdentifiers extends Packet
{
    public static function send(Session $s, Socket $sock): void
    {
        $payload = StaticPacketCache::getInstance()->get('available_actor_identifiers', function () {
            $path = \dirname(__DIR__, 3) . '/resources/entity_identifiers.nbt';
            $data = @file_get_contents($path);
            if ($data === false) {
                Logger::error("entity_identifiers.nbt not found!");
                return '';
            }
            return $data;
        });
        if ($payload === '') {
            return;
        }
        self::sendBatch(ProtocolInfo::AVAILABLE_ACTOR_IDENTIFIERS_PACKET, $payload, $s, $sock);
    }
}
