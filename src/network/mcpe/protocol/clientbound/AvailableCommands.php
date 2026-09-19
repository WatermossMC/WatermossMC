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

final class AvailableCommands extends Packet
{
    public const ARG_FLAG_VALID = 0x100000;
    public const ARG_TYPE_INT = 0x01;
    public const ARG_TYPE_FLOAT = 0x03;
    public const ARG_TYPE_VALUE = 0x04;
    public const ARG_TYPE_STRING = 0xd;

    /**
     * @param array<string, array{description: string, aliases: string[], overloads: array}> $commands
     */
    public static function send(Session $s, Socket $sock, array $commands = []): void
    {
        $payload = '';

        // Enum values (string array)
        $enumValues = [];
        $enumValueMap = [];
        foreach ($commands as $cmdName => $cmdData) {
            foreach ($enumValues as $val) {
                if ($val === $cmdName) {
                    continue 2;
                }
            }
            $enumValueMap[$cmdName] = count($enumValues);
            $enumValues[] = $cmdName;
        }

        $payload .= McpeBinary::writeUnsignedVarInt(count($enumValues));
        foreach ($enumValues as $value) {
            $payload .= McpeBinary::writeString($value);
        }

        // Chained sub command values
        $payload .= McpeBinary::writeUnsignedVarInt(0);

        // Postfixes
        $payload .= McpeBinary::writeUnsignedVarInt(0);

        // Enums
        $payload .= McpeBinary::writeUnsignedVarInt(0);

        // Chained sub command data
        $payload .= McpeBinary::writeUnsignedVarInt(0);

        // Command Data
        $payload .= McpeBinary::writeUnsignedVarInt(count($commands));
        foreach ($commands as $name => $data) {
            $payload .= McpeBinary::writeString($name);
            $payload .= McpeBinary::writeString($data['description'] ?? '');
            $payload .= McpeBinary::writeByte(0); // flags
            $payload .= McpeBinary::writeByte(0); // permission
            $payload .= McpeBinary::writeInt(-1); // alias enum index (-1 for none)

            // Overloads
            $overloads = $data['overloads'] ?? [];
            $payload .= McpeBinary::writeUnsignedVarInt(count($overloads));
            foreach ($overloads as $overload) {
                $parameters = $overload['parameters'] ?? [];
                $payload .= McpeBinary::writeUnsignedVarInt(count($parameters));
                foreach ($parameters as $param) {
                    $payload .= McpeBinary::writeString($param['name'] ?? 'arg');
                    $payload .= McpeBinary::writeUnsignedVarInt($param['type'] ?? (self::ARG_FLAG_VALID | self::ARG_TYPE_STRING));
                    $payload .= McpeBinary::writeBool($param['optional'] ?? false);
                    $payload .= McpeBinary::writeByte(0); // parse rule
                }
            }
        }

        // Soft enums
        $payload .= McpeBinary::writeUnsignedVarInt(0);

        // Enum constraints
        $payload .= McpeBinary::writeUnsignedVarInt(0);

        self::sendBatch(ProtocolInfo::AVAILABLE_COMMANDS_PACKET, $payload, $s, $sock);
    }
}
