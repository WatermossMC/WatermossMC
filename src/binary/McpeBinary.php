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
    public static function writeByte(int $v): string
    {
        return \chr($v & 0xFF);
    }

    public static function writeBool(bool $v): string
    {
        return \chr($v ? 1 : 0);
    }

    /** MCPE ushort = LITTLE endian */
    public static function writeLShort(int $v): string
    {
        return pack('v', $v & 0xFFFF);
    }

    /** MCPE int32 = BIG endian (YES, THIS IS CORRECT) */
    public static function writeInt(int $v): string
    {
        return pack('N', $v);
    }

    public static function writeLInt(int $v): string
    {
        return pack('V', $v);
    }

    /** MCPE float = LITTLE endian */
    public static function writeFloat(float $v): string
    {
        return pack('g', $v);
    }

    /** MCPE long = LITTLE endian */
    public static function writeLLong(int $v): string
    {
        return pack('P', $v);
    }

    public static function writeString(string $v): string
    {
        return self::writeVarInt(\strlen($v)) . $v;
    }

    public static function writeStringInt(string $v): string
    {
        return self::writeInt(\strlen($v)) . $v;
    }

    public static function writeVarInt(int $value): string
    {
        $buf = '';
        $v = $value & 0xFFFFFFFF;

        while (($v & ~0x7F) !== 0) {
            $buf .= \chr(($v & 0x7F) | 0x80);
            $v >>= 7;
        }

        return $buf . \chr($v);
    }

    public static function writeUnsignedVarLong(int $v): string
    {
        $buf = '';
        for ($i = 0; $i < 10; ++$i) {
            $byte = $v & 0x7F;
            $v >>= 7;
            if ($v !== 0) {
                $byte |= 0x80;
            }
            $buf .= \chr($byte);
            if ($v === 0) {
                break;
            }
        }
        return $buf;
    }

    public static function writeSignedVarLong(int $value): string
    {
        $buf = '';
        $v = $value;

        while (($v & ~0x7F) !== 0) {
            $buf .= \chr(($v & 0x7F) | 0x80);
            $v >>= 7;
        }

        return $buf . \chr($v);
    }

    public static function writeSignedVarInt(int $value): string
    {
        $v = ($value << 1) ^ ($value >> 31);
        return self::writeVarInt($v);
    }

    public static function writeUUID(string $uuid): string
    {
        $bytes = \Ramsey\Uuid\Uuid::fromString($uuid)->getBytes();
        return strrev(substr($bytes, 0, 8)) . strrev(substr($bytes, 8, 8));
    }

    public static function readByte(string $buf, int &$o): int
    {
        return \ord($buf[$o++]);
    }

    public static function readBool(string $buf, int &$o): bool
    {
        return self::readByte($buf, $o) !== 0;
    }

    public static function readLShort(string $buf, int &$o): int
    {
        $r = unpack('v', substr($buf, $o, 2));
        if ($r === false || !isset($r[1])) {
            throw new RuntimeException('unpack failed');
        }
        $o += 2;
        /** @var int $value */
        $value = $r[1];
        return $value;
    }

    public static function readInt(string $buf, int &$o): int
    {
        $r = unpack('N', substr($buf, $o, 4));
        if ($r === false || !isset($r[1])) {
            throw new RuntimeException('unpack int failed');
        }
        $o += 4;
        /** @var int $value */
        $value = $r[1];
        return $value;
    }

    public static function readFloat(string $buf, int &$o): float
    {
        $r = unpack('g', substr($buf, $o, 4));
        if ($r === false || !isset($r[1])) {
            throw new RuntimeException('unpack float failed');
        }
        $o += 4;
        /** @var float $value */
        $value = $r[1];
        return $value;
    }

    public static function readString(string $buf, int &$o): string
    {
        $len = self::readVarInt($buf, $o);
        $v = substr($buf, $o, $len);
        $o += $len;
        return $v;
    }

    public static function readVarInt(string $buf, int &$o): int
    {
        $value = 0;
        $shift = 0;
        $len = \strlen($buf);

        while (true) {
            if ($o >= $len) {
                throw new RuntimeException("VarInt overflow");
            }

            $b = \ord($buf[$o++]);
            $value |= ($b & 0x7F) << $shift;

            if (($b & 0x80) === 0) {
                break;
            }

            $shift += 7;
            if ($shift > 35) {
                throw new RuntimeException("VarInt too big");
            }
        }

        return $value;
    }

    public static function readLInt(string $buf, int &$o): int
    {
        $r = unpack('V', substr($buf, $o, 4));
        if ($r === false || !isset($r[1])) {
            throw new RuntimeException('unpack lint failed');
        }
        $o += 4;
        /** @var int $value */
        $value = $r[1];
        return $value;
    }
}
