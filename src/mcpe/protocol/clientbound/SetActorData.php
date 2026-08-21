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
use watermossmc\entity\Entity;
use watermossmc\mcpe\network\Session;

final class SetActorData extends Packet
{
    // Metadata keys
    public const DATA_FLAGS = 0;
    public const DATA_NAMETAG = 4;
    public const DATA_AIR = 7;
    public const DATA_MAX_AIR = 42;
    public const DATA_BOUNDING_BOX_WIDTH = 54;
    public const DATA_BOUNDING_BOX_HEIGHT = 55;

    // Metadata types
    public const TYPE_BYTE = 0;
    public const TYPE_SHORT = 1;
    public const TYPE_INT = 2;
    public const TYPE_FLOAT = 3;
    public const TYPE_STRING = 4;
    public const TYPE_LONG = 7;

    // Entity flags (bit positions in DATA_FLAGS long)
    public const FLAG_NO_AI = 16;
    public const FLAG_CAN_CLIMB = 19;
    public const FLAG_BREATHING = 35;

    public static function sendPlayer(Session $s, Socket $sock): void
    {
        // flags: breathing + can climb
        $flags = 0;
        $flags |= 1 << self::FLAG_BREATHING;
        $flags |= 1 << self::FLAG_CAN_CLIMB;
        $entries = [[self::DATA_FLAGS, self::TYPE_LONG, $flags], [self::DATA_AIR, self::TYPE_SHORT, 400], [self::DATA_MAX_AIR, self::TYPE_SHORT, 400], [self::DATA_BOUNDING_BOX_WIDTH, self::TYPE_FLOAT, 0.6], [self::DATA_BOUNDING_BOX_HEIGHT, self::TYPE_FLOAT, 1.8]];
        self::send($s, $sock, $entries);
    }

    public static function send(Entity $entity, Session $s, Socket $sock, int $tick = 0): void
    {
        $payload = '';
        $payload .= Binary::writeUnsignedVarLong($entity->getRuntimeId());
        // actorRuntimeId
        $payload .= $entity->getEntityData()->encodeMetadata();
        // PropertySyncData
        $payload .= Binary::writeVarInt(0);
        $payload .= Binary::writeVarInt(0);
        // tick
        $payload .= Binary::writeVarLong($tick);
        self::sendBatch(ProtocolInfo::SET_ACTOR_DATA_PACKET, $payload, $s, $sock);
    }
}
