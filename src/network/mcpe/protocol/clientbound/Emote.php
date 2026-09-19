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

final class Emote extends Packet
{
    public const FLAG_SERVER = 1 << 0;
    public const FLAG_MUTE_ANNOUNCEMENT = 1 << 1;

    public static function send(
        Session $s,
        Socket $sock,
        int $actorRuntimeId,
        string $emoteId,
        int $emoteLengthTicks,
        string $xboxUserId,
        string $platformChatId,
        int $flags
    ): void {
        $payload = McpeBinary::writeUnsignedVarInt($actorRuntimeId);
        $payload .= McpeBinary::writeString($emoteId);
        $payload .= McpeBinary::writeUnsignedVarInt($emoteLengthTicks);
        $payload .= McpeBinary::writeString($xboxUserId);
        $payload .= McpeBinary::writeString($platformChatId);
        $payload .= McpeBinary::writeByte($flags);
        self::sendBatch(ProtocolInfo::EMOTE_PACKET, $payload, $s, $sock);
    }
}
