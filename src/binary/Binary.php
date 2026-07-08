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

declare (strict_types=1);

namespace watermossmc\binary;

use Ramsey\Uuid\Uuid;
use RuntimeException;

final class Binary
{
    public static function writeByte(int $v): string
    {
        return \chr($v & 0xff);
    }

    public static function writeBool(bool $v): string
    {
        return \chr($v ? 1 : 0);
    }

    public static function writeShort(int $v): string
    {
        return pack('n', $v);
    }

    public static function writeLShort(int $v): string
    {
        return pack('v', $v & 0xffff);
    }

    public static function writeInt(int $v): string
    {
        return pack('N', $v);
    }

    public static function writeLInt(int $v): string
    {
        return pack('V', $v);
    }

    public static function writeLong(int $v): string
    {
        return pack('J', $v);
    }

    public static function writeLLong(int $v): string
    {
        return pack('P', $v);
    }

    public static function writeTriad(int $v): string
    {
        return \chr($v & 0xff) . \chr($v >> 8 & 0xff) . \chr($v >> 16 & 0xff);
    }

    public static function writeFloat(float $v): string
    {
        return pack('g', $v);
    }

    public static function writeLFloat(float $v): string
    {
        return pack('g', $v);
    }

    public static function writeString(string $v): string
    {
        return self::writeShort(\strlen($v)) . $v;
    }

    public static function readString(string $buf, int &$o): string
    {
        $len = self::readShort($buf, $o);
        self::ensure($buf, $o, $len);
        $v = substr($buf, $o, $len);
        $o += $len;
        return $v;
    }

    public static function writeStringInt(string $v): string
    {
        return self::writeInt(\strlen($v)) . $v;
    }

    public static function writeVarInt(int $value): string
    {
        $buf = '';
        $v = $value & 0xffffffff;
        while (($v & ~0x7f) !== 0) {
            $buf .= \chr($v & 0x7f | 0x80);
            $v >>= 7;
        }
        return $buf . \chr($v);
    }

    public static function writeVarLong(int $value): string
    {
        $buf = '';
        $v = $value;
        while (($v & ~0x7f) !== 0) {
            $buf .= \chr($v & 0x7f | 0x80);
            $v >>= 7;
        }
        return $buf . \chr($v);
    }

    public static function writeUnsignedVarLong(int $v): string
    {
        $buf = '';
        for ($i = 0; $i < 10; ++$i) {
            $byte = $v & 0x7f;
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

    public static function writeUUID(string $uuid): string
    {
        $bytes = Uuid::fromString($uuid)->getBytes();
        return strrev(substr($bytes, 0, 8)) . strrev(substr($bytes, 8, 8));
    }

    public static function writeUShortBE(int $v): string
    {
        return pack('n', $v & 0xffff);
    }

    public static function writeUInt8(int $v): string
    {
        return \chr($v & 0xff);
    }

    public static function writeFloatBE(float $v): string
    {
        return pack('G', $v);
    }

    private static function ensure(string $buf, int $o, int $need): void
    {
        if (\strlen($buf) < $o + $need) {
            throw new RuntimeException('Binary buffer underrun');
        }
    }

    public static function readByte(string $buf, int &$o): int
    {
        self::ensure($buf, $o, 1);
        return \ord($buf[$o++]);
    }

    public static function readBool(string $buf, int &$o): bool
    {
        return self::readByte($buf, $o) !== 0;
    }

    public static function readShort(string $buf, int &$o): int
    {
        self::ensure($buf, $o, 2);
        $r = unpack('n', substr($buf, $o, 2));
        if ($r === false || !isset($r[1])) {
            throw new RuntimeException('unpack short failed');
        }
        $o += 2;
        /** @var int $value */
        $value = $r[1];
        return $value;
    }

    public static function readLShort(string $buf, int &$o): int
    {
        self::ensure($buf, $o, 2);
        $r = unpack('v', substr($buf, $o, 2));
        if ($r === false || !isset($r[1])) {
            throw new RuntimeException('unpack lshort failed');
        }
        $o += 2;
        /** @var int $value */
        $value = $r[1];
        return $value;
    }

    public static function readInt(string $buf, int &$o): int
    {
        self::ensure($buf, $o, 4);
        $r = unpack('N', substr($buf, $o, 4));
        if ($r === false || !isset($r[1])) {
            throw new RuntimeException('unpack int failed');
        }
        $o += 4;
        /** @var int $value */
        $value = $r[1];
        return $value;
    }

    public static function readLInt(string $buf, int &$o): int
    {
        self::ensure($buf, $o, 4);
        $r = unpack('V', substr($buf, $o, 4));
        if ($r === false || !isset($r[1])) {
            throw new RuntimeException('unpack lint failed');
        }
        $o += 4;
        /** @var int $value */
        $value = $r[1];
        return $value;
    }

    public static function readLong(string $buf, int &$o): int
    {
        self::ensure($buf, $o, 8);
        $r = unpack('J', substr($buf, $o, 8));
        if ($r === false || !isset($r[1])) {
            throw new RuntimeException('unpack long failed');
        }
        $o += 8;
        /** @var int $value */
        $value = $r[1];
        return $value;
    }

    public static function readTriad(string $buf, int &$o): int
    {
        self::ensure($buf, $o, 3);
        $b0 = \ord($buf[$o]);
        $b1 = \ord($buf[$o + 1]);
        $b2 = \ord($buf[$o + 2]);
        $o += 3;
        return $b0 | $b1 << 8 | $b2 << 16;
    }

    public static function readFloat(string $buf, int &$o): float
    {
        self::ensure($buf, $o, 4);
        $r = unpack('g', substr($buf, $o, 4));
        if ($r === false || !isset($r[1])) {
            throw new RuntimeException('unpack float failed');
        }
        $o += 4;
        /** @var float $value */
        $value = $r[1];
        return $value;
    }

    public static function readStringInt(string $buf, int &$o): string
    {
        $len = self::readInt($buf, $o);
        self::ensure($buf, $o, $len);
        $v = substr($buf, $o, $len);
        $o += $len;
        return $v;
    }

    /**
     * @return bool[]
     */
    public static function readBitSet(string $buf, int &$o, int $bits): array
    {
        $bytes = intdiv($bits + 7, 8);
        self::ensure($buf, $o, $bytes);
        $data = substr($buf, $o, $bytes);
        $o += $bytes;
        $flags = [];
        for ($i = 0; $i < $bits; $i++) {
            $byte = \ord($data[$i >> 3]);
            $flags[$i] = ($byte >> ($i & 7) & 1) === 1;
        }
        return $flags;
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
            $value |= ($b & 0x7f) << $shift;
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

    public static function readVarLong(string $buf, int &$o): int
    {
        $value = 0;
        $shift = 0;
        while (true) {
            self::ensure($buf, $o, 1);
            $b = \ord($buf[$o++]);
            $value |= ($b & 0x7f) << $shift;
            if (($b & 0x80) === 0) {
                break;
            }
            $shift += 7;
            if ($shift > 70) {
                throw new RuntimeException('VarLong too big');
            }
        }
        return $value;
    }

    /**
     * @return array{float, float}
     */
    public static function readVector2(string $buf, int &$o): array
    {
        return [self::readFloat($buf, $o), self::readFloat($buf, $o)];
    }

    /**
     * @return array{float, float, float}
     */
    public static function readVector3(string $buf, int &$o): array
    {
        return [self::readFloat($buf, $o), self::readFloat($buf, $o), self::readFloat($buf, $o)];
    }

    public static function skipItemInteractionData(string $buf, int &$o): void {}

    public static function skipItemStackRequest(string $buf, int &$o): void {}

    public static function skipBlockActions(string $buf, int &$o): void {}

    public static function skipVehicleInfo(string $buf, int &$o): void {}
}
