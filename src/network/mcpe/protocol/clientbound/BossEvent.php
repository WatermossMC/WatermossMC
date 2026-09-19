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

final class BossEvent extends Packet
{
    public const TYPE_SHOW = 0;
    public const TYPE_REGISTER_PLAYER = 1;
    public const TYPE_HIDE = 2;
    public const TYPE_UNREGISTER_PLAYER = 3;
    public const TYPE_HEALTH_PERCENT = 4;
    public const TYPE_TITLE = 5;
    public const TYPE_PROPERTIES = 6;
    public const TYPE_TEXTURE = 7;
    public const TYPE_QUERY = 8;

    public static function send(
        Session $s,
        Socket $sock,
        int $bossActorUniqueId,
        int $eventType,
        float $healthPercent = 0.0,
        string $title = "",
        string $filteredTitle = "",
        int $color = 0,
        int $overlay = 0
    ): void {
        $payload = McpeBinary::writeSignedVarInt($bossActorUniqueId);
        $payload .= McpeBinary::writeByte($eventType);
        $payload .= McpeBinary::writeString($title);
        $payload .= McpeBinary::writeString($filteredTitle);
        $payload .= pack('f', $healthPercent);
        $payload .= McpeBinary::writeByte($color);
        $payload .= McpeBinary::writeByte($overlay);
        self::sendBatch(ProtocolInfo::BOSS_EVENT_PACKET, $payload, $s, $sock);
    }
}
