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

final class UpdateBlock
{
    public static function send(
        Session $session,
        Socket $socket,
        int $x,
        int $y,
        int $z,
        int $blockRuntimeId,
        int $flags,
        int $dataLayerId,
    ): void {
        $payload = McpeBinary::writeSignedVarInt($x);
        $payload .= McpeBinary::writeSignedVarInt($y);
        $payload .= McpeBinary::writeSignedVarInt($z);
        $payload .= McpeBinary::writeUnsignedVarInt($blockRuntimeId);
        $payload .= McpeBinary::writeUnsignedVarInt($flags);
        $payload .= McpeBinary::writeUnsignedVarInt($dataLayerId);

        Packet::sendBatch(ProtocolInfo::UPDATE_BLOCK_PACKET, $payload, $session, $socket);
    }
}
