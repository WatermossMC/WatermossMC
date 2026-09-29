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
use watermossmc\binary\McpeBinary;
use watermossmc\network\mcpe\protocol\Packet;
use watermossmc\network\mcpe\protocol\ProtocolInfo;
use watermossmc\network\Session;

final class Animate extends Packet
{
    public const ACTION_SWING_ARM = 1;
    public const ACTION_STOP_SLEEP = 3;
    public const ACTION_CRITICAL_HIT = 4;
    public const ACTION_MAGICAL_CRITICAL_HIT = 5;

    public static function send(Session $s, Socket $sock, int $actorRuntimeId, int $action, float $data = 0.0, ?string $swingSource = null): void
    {
        $payload = McpeBinary::writeByte($action);
        $payload .= McpeBinary::writeUnsignedVarLong($actorRuntimeId);
        $payload .= McpeBinary::writeFloat($data);
        $payload .= McpeBinary::writeOptional(
            $swingSource !== null ? McpeBinary::writeString($swingSource) : null
        );
        self::sendBatch(ProtocolInfo::ANIMATE_PACKET, $payload, $s, $sock);
    }
}
