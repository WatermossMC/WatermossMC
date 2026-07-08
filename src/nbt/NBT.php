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
<<<<<<< HEAD
=======

use RuntimeException;
>>>>>>> 866a1c0 (...)

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
    public const TAG_LONG_ARRAY = 12;

    /**
     * @param array<int|string, mixed> $data
     */
    public static function compound(array $data): string
    {
        $buf = \chr(self::TAG_COMPOUND);
        $buf .= self::writeString('');

        foreach ($data as $name => $value) {
            $buf .= self::writeNamedTag((string)$name, $value);
        }

        return $buf . \chr(self::TAG_END);
    }

    /**
     * @param string $data
     * @return array<string, mixed>
     */
    public static function parse(string $data): array
    {
        $offset = 0;
        [$tag, $name, $value] = self::readNamedTag($data, $offset);

        if ($tag !== self::TAG_COMPOUND) {
            throw new RuntimeException('NBT root tag must be a compound');
        }

        if (!\is_array($value)) {
<<<<<<< HEAD
            throw new \RuntimeException('NBT root value must be a compound');
=======
            throw new RuntimeException('NBT root value must be a compound');
>>>>>>> 866a1c0 (...)
        }

        return $value;
    }

    private static function writeNamedTag(string $name, mixed $value): string
    {
        [$tag, $payload] = self::detectTag($value);

        return \chr($tag)
            . self::writeString($name)
            . $payload;
    }

    /**
     * @param array<mixed> $value
     * @return array{__nbt_type: int, __nbt_value: array<mixed>}
     */
    public static function tagCompound(array $value): array
    {
        return [
            '__nbt_type' => self::TAG_COMPOUND,
            '__nbt_value' => $value,
        ];
    }

    /**
     * @param array<mixed> $value
     * @return array{__nbt_type: int, __nbt_value: array<mixed>}
     */
    public static function tagList(array $value): array
    {
        return [
            '__nbt_type' => self::TAG_LIST,
            '__nbt_value' => $value,
        ];
    }

    /**
     * @return array{0:int,1:string}
     */
    private static function detectTag(mixed $value): array
    {
        if (
            \is_array($value) &&
            isset($value['__nbt_type'], $value['__nbt_value'])
        ) {
            return match ($value['__nbt_type']) {
                self::TAG_COMPOUND => [
                    self::TAG_COMPOUND,
                    self::writeCompoundPayload($value['__nbt_value']),
                ],

                self::TAG_LIST => self::writeList($value['__nbt_value']),

<<<<<<< HEAD
                default => throw new \RuntimeException(
=======
                default => throw new RuntimeException(
>>>>>>> 866a1c0 (...)
                    'Unsupported explicit NBT tag'
                )
            };
        }

        if (\is_int($value)) {
            if ($value < -2147483648 || $value > 2147483647) {
                return [self::TAG_LONG, self::writeLong($value)];
            }

            return [self::TAG_INT, self::writeInt($value)];
        }

        if (\is_float($value)) {
            return [self::TAG_FLOAT, self::writeFloat($value)];
        }

        if (\is_string($value)) {
            return [self::TAG_STRING, self::writeString($value)];
        }

        if (\is_bool($value)) {
            return [self::TAG_BYTE, \chr($value ? 1 : 0)];
        }

        if (\is_array($value)) {
            if (self::isList($value)) {
                return self::writeList($value);
            }

            return [
                self::TAG_COMPOUND,
                self::writeCompoundPayload($value),
            ];
        }

        throw new RuntimeException('Unsupported NBT type');
    }

    /**
     * @param array<mixed, mixed> $data
     */
    private static function writeCompoundPayload(array $data): string
    {
        $buf = '';

        foreach ($data as $name => $value) {
            $buf .= self::writeNamedTag((string)$name, $value);
        }

        return $buf . \chr(self::TAG_END);
    }

    /**
     * @param array<mixed> $list
     * @return array{0:int,1:string}
     */
    private static function writeList(array $list): array
    {
        if ($list === []) {
            return [
                self::TAG_LIST,
                \chr(self::TAG_END) . pack('N', 0),
            ];
        }

        [$childTag] = self::detectTag($list[0]);

        $buf = \chr($childTag);
        $buf .= pack('N', \count($list));

        foreach ($list as $value) {
            [$tag, $payload] = self::detectTag($value);

            if ($tag !== $childTag) {
<<<<<<< HEAD
                throw new \RuntimeException(
=======
                throw new RuntimeException(
>>>>>>> 866a1c0 (...)
                    'NBT list contains mixed tag types'
                );
            }

            $buf .= $payload;
        }

        return [self::TAG_LIST, $buf];
    }

    private static function writeString(string $v): string
    {
        return pack('n', \strlen($v)) . $v;
    }

    private static function writeInt(int $v): string
    {
        return pack('N', $v);
    }

    private static function writeLong(int $v): string
    {
        $hi = ($v >> 32) & 0xFFFFFFFF;
        $lo = $v & 0xFFFFFFFF;
        return pack('N2', $hi, $lo);
    }

    private static function writeFloat(float $v): string
    {
        return pack('G', $v);
    }

    private static function writeDouble(float $v): string
    {
        $data = pack('d', $v);
        if (self::isLittleEndian()) {
            $data = strrev($data);
        }
        return $data;
    }

    /**
     * @return array{0: int, 1: string, 2: mixed}
     */
    private static function readNamedTag(string $buf, int &$o): array
    {
        $tag = self::readByte($buf, $o);

        if ($tag === self::TAG_END) {
            return [self::TAG_END, '', null];
        }

        $name = self::readString($buf, $o);
        $payload = self::readPayload($tag, $buf, $o);

        return [$tag, $name, $payload];
    }

    private static function readPayload(int $tag, string $buf, int &$o): mixed
    {
        return match ($tag) {
            self::TAG_BYTE => self::readByte($buf, $o),
            self::TAG_SHORT => self::readShort($buf, $o),
            self::TAG_INT => self::readInt($buf, $o),
            self::TAG_LONG => self::readLong($buf, $o),
            self::TAG_FLOAT => self::readFloat($buf, $o),
            self::TAG_DOUBLE => self::readDouble($buf, $o),
            self::TAG_BYTE_ARRAY => self::readByteArray($buf, $o),
            self::TAG_STRING => self::readString($buf, $o),
            self::TAG_LIST => self::readList($buf, $o),
            self::TAG_COMPOUND => self::readCompound($buf, $o),
            self::TAG_INT_ARRAY => self::readIntArray($buf, $o),
            self::TAG_LONG_ARRAY => self::readLongArray($buf, $o),
            default => throw new RuntimeException('Unsupported NBT payload type: ' . $tag),
        };
    }

    private static function readString(string $buf, int &$o): string
    {
        $length = self::readShort($buf, $o);
        self::ensure($buf, $o, $length);

        $value = substr($buf, $o, $length);
        $o += $length;

        return $value;
    }

    private static function readByte(string $buf, int &$o): int
    {
        self::ensure($buf, $o, 1);
        return \ord($buf[$o++]);
    }

    private static function readShort(string $buf, int &$o): int
    {
        self::ensure($buf, $o, 2);
        $value = unpack('n', substr($buf, $o, 2));
        if ($value === false) {
            throw new RuntimeException('Failed to unpack short');
        }
        $o += 2;
        return $value[1];
    }

    private static function readInt(string $buf, int &$o): int
    {
        self::ensure($buf, $o, 4);
        $value = unpack('N', substr($buf, $o, 4));
        if ($value === false) {
            throw new RuntimeException('Failed to unpack int');
        }
        $o += 4;
        return $value[1];
    }

    private static function readLong(string $buf, int &$o): int
    {
        self::ensure($buf, $o, 8);
        $value = unpack('N2', substr($buf, $o, 8));
        if ($value === false) {
            throw new RuntimeException('Failed to unpack long');
        }
        $o += 8;
        return ($value[1] << 32) | $value[2];
    }

    private static function readFloat(string $buf, int &$o): float
    {
        self::ensure($buf, $o, 4);
        $value = unpack('G', substr($buf, $o, 4));
        if ($value === false) {
            throw new RuntimeException('Failed to unpack float');
        }
        $o += 4;
        return $value[1];
    }

    private static function readDouble(string $buf, int &$o): float
    {
        self::ensure($buf, $o, 8);
        $data = substr($buf, $o, 8);
        if (self::isLittleEndian()) {
            $data = strrev($data);
        }
        $value = unpack('d', $data);
        if ($value === false) {
            throw new RuntimeException('Failed to unpack double');
        }
        $o += 8;
        return $value[1];
    }

    /**
     * @return array<int, int>
     */
    private static function readByteArray(string $buf, int &$o): array
    {
        $length = self::readInt($buf, $o);
        self::ensure($buf, $o, $length);

        $data = substr($buf, $o, $length);
        $o += $length;

        return array_map('ord', str_split($data));
    }

    /**
     * @return array<int, int>
     */
    private static function readIntArray(string $buf, int &$o): array
    {
        $length = self::readInt($buf, $o);
        $result = [];

        for ($i = 0; $i < $length; $i++) {
            $result[] = self::readInt($buf, $o);
        }

        return $result;
    }

    /**
     * @return array<int, int>
     */
    private static function readLongArray(string $buf, int &$o): array
    {
        $length = self::readInt($buf, $o);
        $result = [];

        for ($i = 0; $i < $length; $i++) {
            $result[] = self::readLong($buf, $o);
        }

        return $result;
    }

    /**
     * @return array<int, mixed>
     */
    private static function readList(string $buf, int &$o): array
    {
        $childTag = self::readByte($buf, $o);
        $length = self::readInt($buf, $o);

        $result = [];
        for ($i = 0; $i < $length; $i++) {
            if ($childTag === self::TAG_COMPOUND) {
                $result[] = self::readCompound($buf, $o);
            } else {
                $result[] = self::readPayload($childTag, $buf, $o);
            }
        }

        return $result;
    }

    /**
     * @return array<string, mixed>
     */
    private static function readCompound(string $buf, int &$o): array
    {
        $result = [];

        while (true) {
            $tag = self::readByte($buf, $o);
            if ($tag === self::TAG_END) {
                break;
            }

            $name = self::readString($buf, $o);
            $result[$name] = self::readPayload($tag, $buf, $o);
        }

        return $result;
    }

    private static function ensure(string $buf, int $offset, int $length): void
    {
        if (\strlen($buf) < $offset + $length) {
<<<<<<< HEAD
            throw new \RuntimeException('NBT buffer underrun');
=======
            throw new RuntimeException('NBT buffer underrun');
>>>>>>> 866a1c0 (...)
        }
    }

    private static function isLittleEndian(): bool
    {
        return pack('S', 1) === "\x01\x00";
    }

    /**
     * @param array<mixed> $arr
     */
    private static function isList(array $arr): bool
    {
        return array_keys($arr) === range(0, \count($arr) - 1);
    }
}
