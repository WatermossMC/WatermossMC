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

namespace watermossmc\binary;

use RuntimeException;

final class McpeBinary
{

    public static function writeByte(int $value): string
    {
        return chr($value & 0xFF);
    }

    public static function writeBool(bool $value): string
    {
        return self::writeByte($value ? 1 : 0);
    }

    public static function writeShort(int $value): string
    {
        return pack('n', $value & 0xFFFF);
    }

    public static function writeLShort(int $value): string
    {
        return pack('v', $value & 0xFFFF);
    }

    public static function writeInt(int $value): string
    {
        return pack('N', $value & 0xFFFFFFFF);
    }

    public static function writeLInt(int $value): string
    {
        return pack('V', $value & 0xFFFFFFFF);
    }

    public static function writeLong(int $value): string
    {
        return pack('J', $value);
    }

    public static function writeLLong(int $value): string
    {
        return pack('q', $value);
    }

    public static function writeFloat(float $value): string
    {
        return pack('g', $value);
    }

    public static function writeBFloat(float $value): string
    {
        return pack('G', $value);
    }

    public static function writeDouble(float $value): string
    {
        return pack('e', $value);
    }

    public static function writeBDouble(float $value): string
    {
        return pack('E', $value);
    }

    public static function writeString(string $value): string
    {
        return self::writeVarInt(strlen($value)) . $value;
    }

    public static function writeStringInt(string $value): string
    {
        return self::writeLInt(strlen($value)) . $value;
    }

    public static function writeVarInt(int $value): string
    {
        $value &= 0xFFFFFFFF;

        $buffer = '';

        do {
            $byte = $value & 0x7F;
            $value >>= 7;

            if ($value !== 0) {
                $byte |= 0x80;
            }

            $buffer .= chr($byte);
        } while ($value !== 0);

        return $buffer;
    }

    public static function writeUnsignedVarInt(int $value): string
    {

        return self::writeVarInt($value);
    }

    public static function writeSignedVarInt(int $value): string
    {
        $encoded = ($value << 1) ^ ($value >> 31);

        return self::writeVarInt($encoded);
    }

    public static function writeUnsignedVarLong(int $value): string
    {
        $buffer = '';

        for ($i = 0; $i < 10; ++$i) {
            $byte = $value & 0x7F;
            $value >>= 7;

            if ($value !== 0) {
                $byte |= 0x80;
            }

            $buffer .= chr($byte);

            if ($value === 0) {
                return $buffer;
            }
        }

        throw new RuntimeException('Unsigned VarLong overflow');
    }

    public static function writeSignedVarLong(int $value): string
    {
        $encoded = ($value << 1) ^ ($value >> 63);

        return self::writeUnsignedVarLong($encoded);
    }

    public static function writeUUID(string $uuid): string
    {
        $bytes = \Ramsey\Uuid\Uuid::fromString($uuid)->getBytes();

        return strrev(substr($bytes, 0, 8))
            . strrev(substr($bytes, 8, 8));
    }

    private static function requireBytes(
        string $buffer,
        int $offset,
        int $length
    ): void {
        if ($length < 0 || $offset < 0 || $offset + $length > strlen($buffer)) {
            throw new RuntimeException(
                "Unexpected end of buffer: need {$length} byte(s)"
            );
        }
    }

    public static function readByte(string $buffer, int &$offset): int
    {
        self::requireBytes($buffer, $offset, 1);

        return ord($buffer[$offset++]);
    }

    public static function readBool(string $buffer, int &$offset): bool
    {
        return self::readByte($buffer, $offset) !== 0;
    }

    public static function readShort(string $buffer, int &$offset): int
    {
        self::requireBytes($buffer, $offset, 2);

        $result = unpack('n', substr($buffer, $offset, 2));

        if ($result === false) {
            throw new RuntimeException('Failed to unpack short');
        }

        $offset += 2;

        return $result[1];
    }

    public static function readLShort(string $buffer, int &$offset): int
    {
        self::requireBytes($buffer, $offset, 2);

        $result = unpack('v', substr($buffer, $offset, 2));

        if ($result === false) {
            throw new RuntimeException('Failed to unpack little-endian short');
        }

        $offset += 2;

        return $result[1];
    }

    public static function readInt(string $buffer, int &$offset): int
    {
        self::requireBytes($buffer, $offset, 4);

        $result = unpack('N', substr($buffer, $offset, 4));

        if ($result === false) {
            throw new RuntimeException('Failed to unpack int');
        }

        $offset += 4;

        return $result[1];
    }

    public static function readLInt(string $buffer, int &$offset): int
    {
        self::requireBytes($buffer, $offset, 4);

        $result = unpack('V', substr($buffer, $offset, 4));

        if ($result === false) {
            throw new RuntimeException(
                'Failed to unpack little-endian int'
            );
        }

        $offset += 4;

        return $result[1];
    }

    public static function readLong(string $buffer, int &$offset): int
    {
        self::requireBytes($buffer, $offset, 8);

        $result = unpack('J', substr($buffer, $offset, 8));

        if ($result === false) {
            throw new RuntimeException('Failed to unpack long');
        }

        $offset += 8;

        return $result[1];
    }

    public static function readLLong(string $buffer, int &$offset): int
    {
        self::requireBytes($buffer, $offset, 8);

        $result = unpack('q', substr($buffer, $offset, 8));

        if ($result === false) {
            throw new RuntimeException(
                'Failed to unpack little-endian long'
            );
        }

        $offset += 8;

        return $result[1];
    }

    public static function readFloat(string $buffer, int &$offset): float
    {
        self::requireBytes($buffer, $offset, 4);

        $result = unpack('g', substr($buffer, $offset, 4));

        if ($result === false) {
            throw new RuntimeException('Failed to unpack float');
        }

        $offset += 4;

        return $result[1];
    }

    public static function readBFloat(string $buffer, int &$offset): float
    {
        self::requireBytes($buffer, $offset, 4);

        $result = unpack('G', substr($buffer, $offset, 4));

        if ($result === false) {
            throw new RuntimeException(
                'Failed to unpack big-endian float'
            );
        }

        $offset += 4;

        return $result[1];
    }

    public static function readDouble(string $buffer, int &$offset): float
    {
        self::requireBytes($buffer, $offset, 8);

        $result = unpack('e', substr($buffer, $offset, 8));

        if ($result === false) {
            throw new RuntimeException('Failed to unpack double');
        }

        $offset += 8;

        return $result[1];
    }

    public static function readBDouble(string $buffer, int &$offset): float
    {
        self::requireBytes($buffer, $offset, 8);

        $result = unpack('E', substr($buffer, $offset, 8));

        if ($result === false) {
            throw new RuntimeException(
                'Failed to unpack big-endian double'
            );
        }

        $offset += 8;

        return $result[1];
    }

    public static function readString(string $buffer, int &$offset): string
    {
        $length = self::readVarInt($buffer, $offset);

        if ($length < 0) {
            throw new RuntimeException('Negative string length');
        }

        self::requireBytes($buffer, $offset, $length);

        $value = substr($buffer, $offset, $length);
        $offset += $length;

        return $value;
    }

    public static function readStringInt(
        string $buffer,
        int &$offset
    ): string {
        $length = self::readLInt($buffer, $offset);

        if ($length < 0) {
            throw new RuntimeException('Negative string length');
        }

        self::requireBytes($buffer, $offset, $length);

        $value = substr($buffer, $offset, $length);
        $offset += $length;

        return $value;
    }

    public static function readVarInt(string $buffer, int &$offset): int
    {
        $value = 0;

        for ($i = 0; $i < 5; ++$i) {
            $byte = self::readByte($buffer, $offset);

            $value |= ($byte & 0x7F) << ($i * 7);

            if (($byte & 0x80) === 0) {
                return $value;
            }
        }

        throw new RuntimeException('VarInt32 overflow');
    }

    public static function readUnsignedVarInt(
        string $buffer,
        int &$offset
    ): int {
        return self::readVarInt($buffer, $offset);
    }

    public static function readSignedVarInt(
        string $buffer,
        int &$offset
    ): int {
        $value = self::readVarInt($buffer, $offset);

        return ($value >> 1) ^ -($value & 1);
    }

    public static function readUnsignedVarLong(
        string $buffer,
        int &$offset
    ): int {
        $value = 0;

        for ($i = 0; $i < 10; ++$i) {
            $byte = self::readByte($buffer, $offset);

            if ($i === 9 && ($byte & 0x7E) !== 0) {
                throw new RuntimeException('VarLong64 overflow');
            }

            $value |= ($byte & 0x7F) << ($i * 7);

            if (($byte & 0x80) === 0) {
                return $value;
            }
        }

        throw new RuntimeException('VarLong64 overflow');
    }

    public static function readSignedVarLong(
        string $buffer,
        int &$offset
    ): int {
        $value = self::readUnsignedVarLong($buffer, $offset);

        return ($value >> 1) ^ -($value & 1);
    }

    public static function readUUID(
        string $buffer,
        int &$offset
    ): string {
        self::requireBytes($buffer, $offset, 16);

        $first = strrev(substr($buffer, $offset, 8));
        $second = strrev(substr($buffer, $offset + 8, 8));

        $offset += 16;

        return \Ramsey\Uuid\Uuid::fromBytes($first . $second)
            ->toString();
    }

    public static function writeBytes(string $value): string
    {
        return $value;
    }

    public static function readBytes(
        string $buffer,
        int &$offset,
        int $length
    ): string {
        self::requireBytes($buffer, $offset, $length);

        $value = substr($buffer, $offset, $length);
        $offset += $length;

        return $value;
    }

    public static function remaining(
        string $buffer,
        int $offset
    ): int {
        if ($offset < 0 || $offset > strlen($buffer)) {
            throw new RuntimeException('Invalid buffer offset');
        }

        return strlen($buffer) - $offset;
    }

    public static function hasRemaining(
        string $buffer,
        int $offset
    ): bool {
        return $offset < strlen($buffer);
    }
}