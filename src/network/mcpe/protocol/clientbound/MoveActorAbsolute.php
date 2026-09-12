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
use watermossmc\binary\McpeBinary;
use watermossmc\entity\Entity;
use watermossmc\network\mcpe\protocol\Packet;
use watermossmc\network\mcpe\protocol\ProtocolInfo;
use watermossmc\network\Session;

final class MoveActorAbsolute
{
    public static function send(Session $s, Socket $sock, Entity $entity): void
    {
        $pos = $entity->getPosition();
        $rot = $entity->getRotation();

        $p = Binary::writeVarLong($entity->getRuntimeId());
        $p .= McpeBinary::writeFloat($pos['x']);
        $p .= McpeBinary::writeFloat($pos['y']);
        $p .= McpeBinary::writeFloat($pos['z']);
        $p .= McpeBinary::writeFloat($rot['pitch']);
        $p .= McpeBinary::writeFloat($rot['yaw']);
        $p .= McpeBinary::writeFloat($rot['yaw']);

        Packet::sendBatch(ProtocolInfo::MOVE_ACTOR_ABSOLUTE_PACKET, $p, $s, $sock);
    }
}
