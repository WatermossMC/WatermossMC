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
use watermossmc\inventory\ItemStack;
use watermossmc\network\mcpe\protocol\Packet;
use watermossmc\network\mcpe\protocol\ProtocolInfo;
use watermossmc\network\Session;

final class AddItemActor extends Packet
{
    public static function send(
        Session $s,
        Socket $sock,
        int $runtimeId,
        ItemStack $item,
        float $x,
        float $y,
        float $z,
        float $motionX,
        float $motionY,
        float $motionZ
    ): void {
        $p = McpeBinary::writeSignedVarLong($runtimeId);
        $p .= McpeBinary::writeUnsignedVarLong($runtimeId);
        $p .= $item->toDescriptor(0)->write();
        $p .= McpeBinary::writeFloat($x);
        $p .= McpeBinary::writeFloat($y);
        $p .= McpeBinary::writeFloat($z);
        $p .= McpeBinary::writeFloat($motionX);
        $p .= McpeBinary::writeFloat($motionY);
        $p .= McpeBinary::writeFloat($motionZ);
        $p .= McpeBinary::writeUnsignedVarInt(0);
        $p .= McpeBinary::writeBool(false);
        self::sendBatch(ProtocolInfo::ADD_ITEM_ACTOR_PACKET, $p, $s, $sock);
    }
}
