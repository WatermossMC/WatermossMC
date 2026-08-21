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

use watermossmc\mcpe\protocol\Packet;
use watermossmc\mcpe\protocol\ProtocolInfo;

use Socket;
use watermossmc\binary\Binary;
use watermossmc\binary\McpeBinary;
use watermossmc\mcpe\network\Session;

final class MobEffect extends Packet
{
    public const EVENT_ADD = 1;
    public const EVENT_MODIFY = 2;
    public const EVENT_REMOVE = 3;

    public static function add(Session $s, Socket $sock, int $effectId, int $amplifier = 0, bool $particles = true, int $duration = 0, bool $ambient = true): void
    {
        self::sendEffect($s, $sock, self::EVENT_ADD, $effectId, $amplifier, $particles, $duration, $ambient);
    }

    public static function remove(Session $s, Socket $sock, int $effectId): void
    {
        self::sendEffect($s, $sock, self::EVENT_REMOVE, $effectId, 0, false, 0, false);
    }

    private static function sendEffect(Session $s, Socket $sock, int $eventId, int $effectId, int $amplifier, bool $particles, int $duration, bool $ambient): void
    {
        $payload = '';
        $payload .= Binary::writeVarLong($s->getRuntimeId());
        // actorRuntimeId
        $payload .= Binary::writeUInt8($eventId);
        // eventId (unsigned byte)
        $payload .= McpeBinary::writeSignedVarInt($effectId);
        // effectId
        $payload .= McpeBinary::writeSignedVarInt($amplifier);
        // amplifier
        $payload .= Binary::writeBool($particles);
        // particles
        $payload .= McpeBinary::writeSignedVarInt($duration);
        // duration
        $payload .= Binary::writeVarLong(0);
        // tick (unsigned varint64)
        $payload .= Binary::writeBool($ambient);
        // ambient
        self::sendBatch(ProtocolInfo::MOB_EFFECT_PACKET, $payload, $s, $sock);
    }
}
