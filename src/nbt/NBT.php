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

namespace watermossmc\nbt;

use RuntimeException;

final class NBT
{
    public const TAG_END = 0;
    public const TAG_BYTE = 1;
    public const TAG_SHORT = 2;
    public const TAG_INT = 3;
    public const TAG_LONG = 4;
    public const TAG_FLOAT = 5;
    public const TAG_DOUBLE = 6;
    public const TAG_BYTE_ARRAY = 7;
    public const TAG_STRING = 8;
    public const TAG_LIST = 9;
    public const TAG_COMPOUND = 10;
    public const TAG_INT_ARRAY = 11;
    public const FORMAT_BIG_ENDIAN = 0;
    public const FORMAT_LITTLE_ENDIAN = 1;
    public const FORMAT_NETWORK = 2;

    /**
     * @param array<int|string, mixed> $data
     */
    public static function compound(array $data, string $name = ''): string
    {
        return self::writeRoot($data, $name, self::FORMAT_BIG_ENDIAN);
    }

    /**
     * @param array<int|string, mixed> $data
     */
    public static function compoundLittle(array $data, string $name = ''): string
    {
        return self::writeRoot($data, $name, self::FORMAT_LITTLE_ENDIAN);
    }

    /**
     * @param array<int|string, mixed> $data
     */
    public static function compoundNetwork(array $data, string $name = ''): string
    {
        return self::writeRoot($data, $name, self::FORMAT_NETWORK);
    }

    public static function parse(string $data, int &$offset = 0): array
    {
        return self::parseInternal($data, self::FORMAT_BIG_ENDIAN, $offset);
    }

    public static function parseLittle(string $data, int &$offset = 0): array
    {
        return self::parseInternal($data, self::FORMAT_LITTLE_ENDIAN, $offset);
    }

    public static function parseNetwork(string $data, int &$offset = 0): array
    {
        return self::parseInternal($data, self::FORMAT_NETWORK, $offset);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function parseMultipleNetwork(string $data): array
    {
        $offset = 0;
        $result = [];

        $length = strlen($data);

        while ($offset < $length) {
            $result[] = self::parseNetwork($data, $offset);
        }

        return $result;
    }

    private static function writeRoot(array $data, string $name, int $format): string
    {
        return \chr(self::TAG_COMPOUND)
            . self::writeString($name, $format)
            . self::writeCompoundPayload($data, $format);
    }

    private static function parseInternal(string $data, int $format, int &$offset = 0): array
    {
        [$tag, $name, $value] = self::readNamedTag($data, $offset, $format);

        if ($tag !== self::TAG_COMPOUND) {
            throw new RuntimeException('NBT root tag must be a compound, got ' . $tag);
        }

        if (!\is_array($value)) {
            throw new RuntimeException('NBT root value must be a compound');
        }

        return $value;
    }

    public static function tagByte(int $value): array
    {
        return ['__nbt_type' => self::TAG_BYTE, '__nbt_value' => $value];
    }

    public static function tagShort(int $value): array
    {
        return ['__nbt_type' => self::TAG_SHORT, '__nbt_value' => $value];
    }

    public static function tagInt(int $value): array
    {
        return ['__nbt_type' => self::TAG_INT, '__nbt_value' => $value];
    }

    public static function tagLong(int $value): array
    {
        return ['__nbt_type' => self::TAG_LONG, '__nbt_value' => $value];
    }

    public static function tagFloat(float $value): array
    {
        return ['__nbt_type' => self::TAG_FLOAT, '__nbt_value' => $value];
    }

    public static function tagDouble(float $value): array
    {
        return ['__nbt_type' => self::TAG_DOUBLE, '__nbt_value' => $value];
    }

    public static function tagByteArray(array $value): array
    {
        return ['__nbt_type' => self::TAG_BYTE_ARRAY, '__nbt_value' => $value];
    }

    public static function tagString(string $value): array
    {
        return ['__nbt_type' => self::TAG_STRING, '__nbt_value' => $value];
    }

    public static function tagIntArray(array $value): array
    {
        return ['__nbt_type' => self::TAG_INT_ARRAY, '__nbt_value' => $value];
    }

    public static function tagCompound(array $value): array
    {
        return ['__nbt_type' => self::TAG_COMPOUND, '__nbt_value' => $value];
    }

    public static function tagList(array $value): array
    {
        return ['__nbt_type' => self::TAG_LIST, '__nbt_value' => $value];
    }

    private static function detectTag(mixed $value, int $format): array
    {
        if (\is_array($value) && isset($value['__nbt_type'], $value['__nbt_value'])) {
            $type = $value['__nbt_type'];
            $data = $value['__nbt_value'];

            return match ($type) {
                self::TAG_BYTE => [self::TAG_BYTE, self::writeByte($data)],
                self::TAG_SHORT => [self::TAG_SHORT, self::writeShort($data, $format)],
                self::TAG_INT => [self::TAG_INT, self::writeInt($data, $format)],
                self::TAG_LONG => [self::TAG_LONG, self::writeLong($data, $format)],
                self::TAG_FLOAT => [self::TAG_FLOAT, self::writeFloat($data, $format)],
                self::TAG_DOUBLE => [self::TAG_DOUBLE, self::writeDouble($data, $format)],
                self::TAG_BYTE_ARRAY => [self::TAG_BYTE_ARRAY, self::writeByteArray($data, $format)],
                self::TAG_STRING => [self::TAG_STRING, self::writeString($data, $format)],
                self::TAG_LIST => self::writeList($data, $format),
                self::TAG_COMPOUND => [self::TAG_COMPOUND, self::writeCompoundPayload($data, $format)],
                self::TAG_INT_ARRAY => [self::TAG_INT_ARRAY, self::writeIntArray($data, $format)],
                default => throw new RuntimeException('Unsupported explicit NBT tag: ' . $type),
            };
        }

        if (\is_bool($value)) {
            return [self::TAG_BYTE, self::writeByte($value ? 1 : 0)];
        }

        if (\is_int($value)) {
            if ($value >= (-0x7fffffff - 1) && $value <= 0x7fffffff) {
                return [self::TAG_INT, self::writeInt($value, $format)];
            }
            return [self::TAG_LONG, self::writeLong($value, $format)];
        }

        if (\is_float($value)) {
            return [self::TAG_DOUBLE, self::writeDouble($value, $format)];
        }

        if (\is_string($value)) {
            return [self::TAG_STRING, self::writeString($value, $format)];
        }

        if (\is_array($value)) {
            if (self::isList($value)) {
                return self::writeList($value, $format);
            }
            return [self::TAG_COMPOUND, self::writeCompoundPayload($value, $format)];
        }

        throw new RuntimeException('Unsupported NBT value type: ' . \get_debug_type($value));
    }

    private static function writeCompoundPayload(array $data, int $format): string
    {
        $buf = '';
        foreach ($data as $name => $value) {
            [$tag, $payload] = self::detectTag($value, $format);
            $buf .= \chr($tag) . self::writeString((string) $name, $format) . $payload;
        }
        return $buf . \chr(self::TAG_END);
    }

    private static function writeList(array $list, int $format): array
    {
        if ($list === []) {
            return [self::TAG_LIST, \chr(self::TAG_END) . self::writeInt(0, $format)];
        }

        [$childTag] = self::detectTag($list[0], $format);
        if ($childTag === self::TAG_END) {
            throw new RuntimeException('NBT list cannot contain TAG_End');
        }

        $buf = \chr($childTag) . self::writeInt(\count($list), $format);
        foreach ($list as $value) {
            [$tag, $payload] = self::detectTag($value, $format);
            if ($tag !== $childTag) {
                throw new RuntimeException('NBT list contains mixed tag types');
            }
            $buf .= $payload;
        }

        return [self::TAG_LIST, $buf];
    }

    private static function writeString(string $value, int $format): string
    {
        $length = \strlen($value);
        if ($length > 32767) {
            throw new RuntimeException('StringTag cannot hold more than 32767 bytes');
        }
        if ($format === self::FORMAT_NETWORK) {
            return self::writeUnsignedVarInt($length) . $value;
        }
        return \pack($format === self::FORMAT_LITTLE_ENDIAN ? 'v' : 'n', $length) . $value;
    }

    private static function writeByte(int $value): string
    {
        return \chr($value & 0xff);
    }

    private static function writeShort(int $value, int $format): string
    {
        return \pack($format !== self::FORMAT_BIG_ENDIAN ? 'v' : 'n', $value);
    }

    private static function writeInt(int $value, int $format): string
    {
        if ($format === self::FORMAT_NETWORK) {
            return self::writeVarInt($value);
        }
        return \pack($format === self::FORMAT_LITTLE_ENDIAN ? 'V' : 'N', $value);
    }

    private static function writeLong(int $value, int $format): string
    {
        if ($format === self::FORMAT_NETWORK) {
            return self::writeVarLong($value);
        }
        return \pack($format === self::FORMAT_LITTLE_ENDIAN ? 'P' : 'J', $value);
    }

    private static function writeFloat(float $value, int $format): string
    {
        return \pack($format !== self::FORMAT_BIG_ENDIAN ? 'g' : 'G', $value);
    }

    private static function writeDouble(float $value, int $format): string
    {
        return \pack($format !== self::FORMAT_BIG_ENDIAN ? 'e' : 'E', $value);
    }

    private static function writeByteArray(array $value, int $format): string
    {
        $buf = self::writeInt(\count($value), $format);
        if ($value !== []) {
            $buf .= \pack('c*', ...$value);
        }
        return $buf;
    }

    private static function writeIntArray(array $value, int $format): string
    {
        $buf = self::writeInt(\count($value), $format);
        if ($value !== []) {
            if ($format === self::FORMAT_NETWORK) {
                foreach ($value as $v) {
                    $buf .= self::writeVarInt($v);
                }
            } else {
                $buf .= \pack($format === self::FORMAT_LITTLE_ENDIAN ? 'V*' : 'N*', ...$value);
            }
        }
        return $buf;
    }

    private static function readNamedTag(string $buffer, int &$offset, int $format): array
    {
        $tag = self::readUnsignedByte($buffer, $offset);
        if ($tag === self::TAG_END) {
            return [self::TAG_END, '', null];
        }

        $name = self::readString($buffer, $offset, $format);
        $value = self::readPayload($tag, $buffer, $offset, $format);
        return [$tag, $name, $value];
    }

    private static function readPayload(int $tag, string $buffer, int &$offset, int $format): mixed
    {
        return match ($tag) {
            self::TAG_BYTE => self::readByte($buffer, $offset),
            self::TAG_SHORT => self::readShort($buffer, $offset, $format),
            self::TAG_INT => self::readInt($buffer, $offset, $format),
            self::TAG_LONG => self::readLong($buffer, $offset, $format),
            self::TAG_FLOAT => self::readFloat($buffer, $offset, $format),
            self::TAG_DOUBLE => self::readDouble($buffer, $offset, $format),
            self::TAG_BYTE_ARRAY => self::readByteArray($buffer, $offset, $format),
            self::TAG_STRING => self::readString($buffer, $offset, $format),
            self::TAG_LIST => self::readList($buffer, $offset, $format),
            self::TAG_COMPOUND => self::readCompound($buffer, $offset, $format),
            self::TAG_INT_ARRAY => self::readIntArray($buffer, $offset, $format),
            default => throw new RuntimeException('Unsupported NBT payload type: ' . $tag . ' (0x' . \dechex($tag) . ') at offset ' . $offset),
        };
    }

    private static function signByte(int $value): int
    {
        return $value << 56 >> 56;
    }

    private static function signShort(int $value): int
    {
        return $value << 48 >> 48;
    }

    private static function signInt(int $value): int
    {
        return $value << 32 >> 32;
    }

    private static function readString(string $buffer, int &$offset, int $format): string
    {
        if ($format === self::FORMAT_NETWORK) {
            $length = self::readUnsignedVarInt($buffer, $offset);
        } else {
            self::ensure($buffer, $offset, 2);
            $bytes = \substr($buffer, $offset, 2);
            $offset += 2;
            $length = \unpack($format === self::FORMAT_LITTLE_ENDIAN ? 'v' : 'n', $bytes)[1];
        }

        if ($length > 32767) {
            throw new RuntimeException('StringTag cannot hold more than 32767 bytes, got ' . $length);
        }
        self::ensure($buffer, $offset, $length);
        $value = \substr($buffer, $offset, $length);
        $offset += $length;
        return $value;
    }

    private static function readUnsignedByte(string $buffer, int &$offset): int
    {
        self::ensure($buffer, $offset, 1);
        return \ord($buffer[$offset++]);
    }

    private static function readByte(string $buffer, int &$offset): int
    {
        self::ensure($buffer, $offset, 1);
        return self::signByte(\ord($buffer[$offset++]));
    }

    private static function readShort(string $buffer, int &$offset, int $format): int
    {
        self::ensure($buffer, $offset, 2);
        $bytes = \substr($buffer, $offset, 2);
        $offset += 2;
        return self::signShort(\unpack($format !== self::FORMAT_BIG_ENDIAN ? 'v' : 'n', $bytes)[1]);
    }

    private static function readInt(string $buffer, int &$offset, int $format): int
    {
        if ($format === self::FORMAT_NETWORK) {
            return self::readVarInt($buffer, $offset);
        }

        self::ensure($buffer, $offset, 4);
        $bytes = \substr($buffer, $offset, 4);
        $offset += 4;
        return self::signInt(\unpack($format === self::FORMAT_LITTLE_ENDIAN ? 'V' : 'N', $bytes)[1]);
    }

    private static function readLong(string $buffer, int &$offset, int $format): int
    {
        if ($format === self::FORMAT_NETWORK) {
            return self::readVarLong($buffer, $offset);
        }

        self::ensure($buffer, $offset, 8);
        $bytes = \substr($buffer, $offset, 8);
        $offset += 8;
        return \unpack($format === self::FORMAT_LITTLE_ENDIAN ? 'P' : 'J', $bytes)[1];
    }

    private static function readFloat(string $buffer, int &$offset, int $format): float
    {
        self::ensure($buffer, $offset, 4);
        $bytes = \substr($buffer, $offset, 4);
        $offset += 4;
        return \unpack($format !== self::FORMAT_BIG_ENDIAN ? 'g' : 'G', $bytes)[1];
    }

    private static function readDouble(string $buffer, int &$offset, int $format): float
    {
        self::ensure($buffer, $offset, 8);
        $bytes = \substr($buffer, $offset, 8);
        $offset += 8;
        return \unpack($format !== self::FORMAT_BIG_ENDIAN ? 'e' : 'E', $bytes)[1];
    }

    private static function readByteArray(string $buffer, int &$offset, int $format): array
    {
        $length = self::readInt($buffer, $offset, $format);
        if ($length < 0) {
            throw new RuntimeException('Negative NBT byte array length');
        }
        if ($length === 0) {
            return [];
        }

        self::ensure($buffer, $offset, $length);
        $bytes = \substr($buffer, $offset, $length);
        $offset += $length;
        return \array_values(\unpack('c*', $bytes) ?: []);
    }

    private static function readIntArray(string $buffer, int &$offset, int $format): array
    {
        $length = self::readInt($buffer, $offset, $format);
        if ($length < 0) {
            throw new RuntimeException('Negative NBT int array length');
        }
        if ($length === 0) {
            return [];
        }

        if ($format === self::FORMAT_NETWORK) {
            $result = [];
            for ($i = 0; $i < $length; ++$i) {
                $result[] = self::readVarInt($buffer, $offset);
            }
            return $result;
        }

        $bytesLength = $length * 4;
        self::ensure($buffer, $offset, $bytesLength);
        $bytes = \substr($buffer, $offset, $bytesLength);
        $offset += $bytesLength;
        return \array_values(\unpack($format === self::FORMAT_LITTLE_ENDIAN ? 'V*' : 'N*', $bytes) ?: []);
    }

    private static function readList(string $buffer, int &$offset, int $format): array
    {
        $childTag = self::readUnsignedByte($buffer, $offset);
        $length = self::readInt($buffer, $offset, $format);

        if ($length < 0) {
            throw new RuntimeException('Negative NBT list length');
        }
        if ($length > 0 && $childTag === self::TAG_END) {
            throw new RuntimeException('Unexpected non-empty list of TAG_End');
        }
        if ($length === 0) {
            return [];
        }

        $result = [];
        for ($i = 0; $i < $length; ++$i) {
            $result[] = self::readPayload($childTag, $buffer, $offset, $format);
        }
        return $result;
    }

    private static function readCompound(string $buffer, int &$offset, int $format): array
    {
        $result = [];
        while (true) {
            $tagOffset = $offset;
            $tag = self::readUnsignedByte($buffer, $offset);
            if ($tag === self::TAG_END) {
                break;
            }

            if ($tag < self::TAG_BYTE || $tag > self::TAG_INT_ARRAY) {
                throw new RuntimeException('Invalid NBT tag type: ' . $tag . ' (0x' . \dechex($tag) . ') at offset ' . $tagOffset);
            }

            $name = self::readString($buffer, $offset, $format);
            $result[$name] = self::readPayload($tag, $buffer, $offset, $format);
        }
        return $result;
    }

    private static function ensure(string $buffer, int $offset, int $length): void
    {
        if ($length < 0) {
            throw new RuntimeException('Negative NBT length');
        }
        $size = \strlen($buffer);
        if ($offset < 0 || $offset > $size || $length > $size - $offset) {
            throw new RuntimeException('NBT buffer underrun at offset ' . $offset . ', requested ' . $length . ' bytes, buffer size ' . $size);
        }
    }

    private static function isList(array $array): bool
    {
        return $array === [] || \array_keys($array) === \range(0, \count($array) - 1);
    }

    private static function readUnsignedVarInt(string $buffer, int &$offset): int
    {
        $value = 0;
        for ($i = 0; $i <= 28; $i += 7) {
            if (!isset($buffer[$offset])) {
                throw new RuntimeException("No bytes left in buffer");
            }
            $b = \ord($buffer[$offset++]);
            $value |= (($b & 0x7f) << $i);
            if (($b & 0x80) === 0) {
                return $value;
            }
        }
        throw new RuntimeException("VarInt did not terminate after 5 bytes!");
    }

    private static function readVarInt(string $buffer, int &$offset): int
    {
        $raw = self::readUnsignedVarInt($buffer, $offset);
        $temp = ((($raw << 63) >> 63) ^ $raw) >> 1;
        return $temp ^ ($raw & (1 << 63));
    }

    private static function readUnsignedVarLong(string $buffer, int &$offset): int
    {
        $value = 0;
        for ($i = 0; $i <= 63; $i += 7) {
            if (!isset($buffer[$offset])) {
                throw new RuntimeException("No bytes left in buffer");
            }
            $b = \ord($buffer[$offset++]);
            $value |= (($b & 0x7f) << $i);
            if (($b & 0x80) === 0) {
                return $value;
            }
        }
        throw new RuntimeException("VarLong did not terminate after 10 bytes!");
    }

    private static function readVarLong(string $buffer, int &$offset): int
    {
        $raw = self::readUnsignedVarLong($buffer, $offset);
        $temp = ((($raw << 63) >> 63) ^ $raw) >> 1;
        return $temp ^ ($raw & (1 << 63));
    }

    private static function writeUnsignedVarInt(int $value): string
    {
        $buf = "";
        $remaining = $value & 0xffffffff;
        for ($i = 0; $i < 5; ++$i) {
            $bits = $remaining & 0x7f;
            if (($remaining >> 7) !== 0) {
                $buf .= \chr($bits | 0x80);
            } else {
                $buf .= \chr($bits & 0x7f);
                return $buf;
            }
            $remaining = (($remaining >> 7) & (\PHP_INT_MAX >> 6));
        }
        throw new RuntimeException("Value too large to be encoded as a VarInt");
    }

    private static function writeVarInt(int $v): string
    {
        $v = ($v << 32 >> 32);
        return self::writeUnsignedVarInt(($v << 1) ^ ($v >> 31));
    }

    private static function writeVarLong(int $v): string
    {
        return self::writeUnsignedVarLong(($v << 1) ^ ($v >> 63));
    }

    private static function writeUnsignedVarLong(int $value): string
    {
        $buf = "";
        $remaining = $value;
        for ($i = 0; $i < 10; ++$i) {
            $bits = $remaining & 0x7f;
            if (($remaining >> 7) !== 0) {
                $buf .= \chr($bits | 0x80);
            } else {
                $buf .= \chr($bits & 0x7f);
                return $buf;
            }
            $remaining = (($remaining >> 7) & (\PHP_INT_MAX >> 6));
        }
        throw new RuntimeException("Value too large to be encoded as a VarLong");
    }
}
