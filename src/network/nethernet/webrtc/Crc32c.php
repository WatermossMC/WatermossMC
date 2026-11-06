<?php

declare(strict_types=1);

namespace watermossmc\network\nethernet\webrtc;

final class Crc32c
{
    private const CHECK_VALUE = 0xE3069283; // CRC32c("123456789")

    /** @var int[]|null */
    private static ?array $table = null;

    private static ?bool $useHash = null;

    public static function compute(string $data): int
    {
        if (self::$useHash === null) {
            self::$useHash = false;
            if (\in_array('crc32c', hash_algos(), true)) {
                $v = unpack('N', hash('crc32c', '123456789', true));
                self::$useHash = $v !== false && $v[1] === self::CHECK_VALUE;
            }
        }
        if (self::$useHash) {
            $v = unpack('N', hash('crc32c', $data, true));
            return $v === false ? 0 : $v[1];
        }
        return self::table($data);
    }

    private static function table(string $data): int
    {
        if (self::$table === null) {
            self::$table = [];
            for ($i = 0; $i < 256; $i++) {
                $c = $i;
                for ($k = 0; $k < 8; $k++) {
                    $c = ($c & 1) ? (($c >> 1) ^ 0x82F63B78) : ($c >> 1);
                }
                self::$table[$i] = $c;
            }
        }
        $crc = 0xFFFFFFFF;
        $len = \strlen($data);
        for ($i = 0; $i < $len; $i++) {
            $crc = self::$table[($crc ^ \ord($data[$i])) & 0xFF] ^ ($crc >> 8);
        }
        return ($crc ^ 0xFFFFFFFF) & 0xFFFFFFFF;
    }
}
