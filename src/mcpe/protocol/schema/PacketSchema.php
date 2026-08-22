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

namespace watermossmc\mcpe\protocol\schema;

use InvalidArgumentException;
use watermossmc\binary\Binary;
use watermossmc\binary\McpeBinary;

final class PacketSchema
{
    /**
     * @param array<string, array{type: string, options?: array}> $fields
     */
    public function __construct(
        private array $fields
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function decode(string $packet, int &$offset): array
    {
        $data = [];
        foreach ($this->fields as $name => $def) {
            $type = $def['type'];
            $data[$name] = match ($type) {
                'varint' => Binary::readVarInt($packet, $offset),
                'varlong' => Binary::readVarLong($packet, $offset),
                'string' => Binary::readString($packet, $offset),
                'bool' => Binary::readBool($packet, $offset),
                'byte' => Binary::readByte($packet, $offset),
                'float' => McpeBinary::readFloat($packet, $offset),
                'double' => McpeBinary::readDouble($packet, $offset),
                'uuid' => (function () use ($packet, &$offset): string {
                    $u1 = McpeBinary::readLLong($packet, $offset);
                    $u2 = McpeBinary::readLLong($packet, $offset);
                    return sprintf('%016x-%016x', $u1, $u2);
                })(),
                'vector3' => (function () use ($packet, &$offset): array {
                    return [
                        'x' => McpeBinary::readFloat($packet, $offset),
                        'y' => McpeBinary::readFloat($packet, $offset),
                        'z' => McpeBinary::readFloat($packet, $offset),
                    ];
                })(),
                'vector2' => (function () use ($packet, &$offset): array {
                    return [
                        'x' => McpeBinary::readFloat($packet, $offset),
                        'y' => McpeBinary::readFloat($packet, $offset),
                    ];
                })(),
                default => throw new InvalidArgumentException("Unknown schema field type: {$type}"),
            };
        }
        return $data;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function encode(array $data): string
    {
        $buffer = '';
        foreach ($this->fields as $name => $def) {
            $type = $def['type'];
            $val = $data[$name] ?? ($def['default'] ?? null);
            $buffer .= match ($type) {
                'varint' => McpeBinary::writeVarInt((int) $val),
                'varlong' => McpeBinary::writeSignedVarLong((int) $val),
                'string' => McpeBinary::writeString((string) $val),
                'bool' => McpeBinary::writeBool((bool) $val),
                'byte' => McpeBinary::writeByte((int) $val),
                'float' => McpeBinary::writeFloat((float) $val),
                'double' => McpeBinary::writeDouble((float) $val),
                'uuid' => (fn (): string => McpeBinary::writeLLong(0) . McpeBinary::writeLLong(0))(),
                default => throw new InvalidArgumentException("Unknown schema field type for encoding: {$type}"),
            };
        }
        return $buffer;
    }
}
