<?php


declare(strict_types=1);

namespace WatermossMC\Binary;

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
        return pack('v', $v & 0xFFFF);
    }

    public static function writeInt(int $v): string
    {
        return pack('N', $v);
    }

    public static function writeLong(int $v): string
    {
        return pack('J', $v);
    }

    public static function writeTriad(int $v): string
    {
        return \chr($v & 0xFF)
             . \chr(($v >> 8) & 0xFF)
             . \chr(($v >> 16) & 0xFF);
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

    public static function writeUUID(string $uuid): string
    {
        return \Ramsey\Uuid\Uuid::fromString($uuid)->getBytes();
    }

    public static function writeUShortBE(int $v): string
    {
        return pack('n', $v & 0xFFFF);
    }

    public static function writeUInt8(int $v): string
    {
        return \chr($v & 0xFF);
    }

    public static function writeFloatBE(float $v): string
    {
        return pack('G', $v);
    }

    private static function ensure(string $buf, int $o, int $need): void
    {
        if (\strlen($buf) < $o + $need) {
            throw new \RuntimeException('Binary buffer underrun');
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
        if ($r === false) {
            throw new \RuntimeException('unpack short failed');
        }
        $o += 2;
        return $r[1];
    }

    public static function readLShort(string $buf, int &$o): int
    {
        self::ensure($buf, $o, 2);
        $r = unpack('v', substr($buf, $o, 2));
        if ($r === false) {
            throw new \RuntimeException('unpack lshort failed');
        }
        $o += 2;
        return $r[1];
    }

    public static function readInt(string $buf, int &$o): int
    {
        self::ensure($buf, $o, 4);
        $r = unpack('N', substr($buf, $o, 4));
        if ($r === false) {
            throw new \RuntimeException('unpack int failed');
        }
        $o += 4;
        return $r[1];
    }

    public static function readLong(string $buf, int &$o): int
    {
        self::ensure($buf, $o, 8);
        $r = unpack('J', substr($buf, $o, 8));
        if ($r === false) {
            throw new \RuntimeException('unpack long failed');
        }
        $o += 8;
        return $r[1];
    }

    public static function readTriad(string $buf, int &$o): int
    {
        self::ensure($buf, $o, 3);

        $b0 = \ord($buf[$o]);
        $b1 = \ord($buf[$o + 1]);
        $b2 = \ord($buf[$o + 2]);

        $o += 3;
        return $b0 | ($b1 << 8) | ($b2 << 16);
    }

    public static function readFloat(string $buf, int &$o): float
    {
        self::ensure($buf, $o, 4);
        $r = unpack('g', substr($buf, $o, 4));
        if ($r === false) {
            throw new \RuntimeException('unpack float failed');
        }
        $o += 4;
        return $r[1];
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
            $flags[$i] = (($byte >> ($i & 7)) & 1) === 1;
        }

        return $flags;
    }

    public static function readVarInt(string $buf, int &$o): int
    {
        $value = 0;
        $shift = 0;

        while (true) {
            self::ensure($buf, $o, 1);
            $b = \ord($buf[$o++]);

            $value |= ($b & 0x7F) << $shift;
            if (($b & 0x80) === 0) {
                break;
            }

            $shift += 7;
            if ($shift > 35) {
                throw new \RuntimeException('VarInt too big');
            }
        }

        if ($value & (1 << 31)) {
            $value -= 1 << 32;
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

            $value |= ($b & 0x7F) << $shift;
            if (($b & 0x80) === 0) {
                break;
            }

            $shift += 7;
            if ($shift > 70) {
                throw new \RuntimeException('VarLong too big');
            }
        }

        return $value;
    }

    /**
     * @return array{float, float}
     */
    public static function readVector2(string $buf, int &$o): array
    {
        return [
            self::readFloat($buf, $o),
            self::readFloat($buf, $o),
        ];
    }

    /**
     * @return array{float, float, float}
     */
    public static function readVector3(string $buf, int &$o): array
    {
        return [
            self::readFloat($buf, $o),
            self::readFloat($buf, $o),
            self::readFloat($buf, $o),
        ];
    }

    public static function skipItemInteractionData(string $buf, int &$o): void {}

    public static function skipItemStackRequest(string $buf, int &$o): void {}

    public static function skipBlockActions(string $buf, int &$o): void {}

    public static function skipVehicleInfo(string $buf, int &$o): void {}
}
