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
use watermossmc\network\mcpe\protocol\types\inventory\FullContainerName;
use watermossmc\network\Session;

final class ItemStackResponse extends Packet
{
    public const RESULT_OK = 0;
    public const RESULT_ERROR = 1;

    /**
     * @param list<array{requestId: int, containers: list<array{containerId: int, slots: list<array{slot: int, count: int, stackId: int}>}>}> $accepted
     * @param list<int> $rejectedRequestIds
     */
    public static function send(Session $s, Socket $sock, array $accepted, array $rejectedRequestIds): void
    {
        $payload = McpeBinary::writeUnsignedVarInt(\count($accepted) + \count($rejectedRequestIds));

        foreach ($accepted as $response) {
            $payload .= McpeBinary::writeByte(self::RESULT_OK);
            $payload .= McpeBinary::writeSignedVarInt($response['requestId']);
            $payload .= McpeBinary::writeBool($response['containers'] !== []);
            if ($response['containers'] !== []) {
                $payload .= McpeBinary::writeUnsignedVarInt(\count($response['containers']));
                foreach ($response['containers'] as $container) {
                    $payload .= (new FullContainerName($container['containerId']))->write();
                    $payload .= McpeBinary::writeUnsignedVarInt(\count($container['slots']));
                    foreach ($container['slots'] as $slot) {
                        $payload .= McpeBinary::writeByte($slot['slot']);
                        $payload .= McpeBinary::writeByte(0); // hotbar slot, unused outside the main inventory
                        $payload .= McpeBinary::writeByte($slot['count']);
                        $hasStackId = $slot['stackId'] !== 0;
                        $payload .= McpeBinary::writeBool($hasStackId);
                        if ($hasStackId) {
                            $payload .= McpeBinary::writeSignedVarInt($slot['stackId']);
                        }
                        $payload .= McpeBinary::writeString(''); // custom name
                        $payload .= McpeBinary::writeBool(false); // filtered custom name absent
                        $payload .= McpeBinary::writeSignedVarInt(0); // durability correction
                    }
                }
            }
        }

        foreach ($rejectedRequestIds as $requestId) {
            $payload .= McpeBinary::writeByte(self::RESULT_ERROR);
            $payload .= McpeBinary::writeSignedVarInt($requestId);
            $payload .= McpeBinary::writeBool(false);
        }

        self::sendBatch(ProtocolInfo::ITEM_STACK_RESPONSE_PACKET, $payload, $s, $sock);
    }
}
