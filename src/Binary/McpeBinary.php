<?php

declare(strict_types=1);

namespace WatermossMC\Binary;

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

    public static function writeVarLong(int $value): string
    {
        $buf = '';
        $v = $value;

        while (($v & ~0x7F) !== 0) {
            $buf .= \chr(($v & 0x7F) | 0x80);
            $v >>= 7;
        }

        return $buf . \chr($v);
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
        if ($r === false) {
            throw new \RuntimeException('unpack failed');
        }
        $o += 2;
        return $r[1];
    }

    public static function readInt(string $buf, int &$o): int
    {
        $r = unpack('N', substr($buf, $o, 4));
        if ($r === false) {
            throw new \RuntimeException('unpack int failed');
        }
        $o += 4;
        return $r[1];
    }

    public static function readFloat(string $buf, int &$o): float
    {
        $r = unpack('g', substr($buf, $o, 4));
        if ($r === false) {
            throw new \RuntimeException('unpack float failed');
        }
        $o += 4;
        return $r[1];
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
                throw new \RuntimeException("VarInt overflow");
            }

            $b = \ord($buf[$o++]);
            $value |= ($b & 0x7F) << $shift;

            if (($b & 0x80) === 0) {
                break;
            }

            $shift += 7;
            if ($shift > 35) {
                throw new \RuntimeException("VarInt too big");
            }
        }

        return $value;
    }

    public static function readLInt(string $buf, int &$o): int
    {
        $r = unpack('V', substr($buf, $o, 4));
        if ($r === false) {
            throw new \RuntimeException('unpack lint failed');
        }
        $o += 4;
        return $r[1];
    }
}
