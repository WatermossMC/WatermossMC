<?php

declare(strict_types=1);

namespace watermossmc\network\nethernet\webrtc;

final class Stun
{
    public const MAGIC = 0x2112A442;

    public const BINDING_REQUEST = 0x0001;

    public const BINDING_SUCCESS = 0x0101;

    public const ATTR_USERNAME = 0x0006;

    public const ATTR_MESSAGE_INTEGRITY = 0x0008;

    public const ATTR_XOR_MAPPED_ADDRESS = 0x0020;

    public const ATTR_USE_CANDIDATE = 0x0025;

    public const ATTR_FINGERPRINT = 0x8028;

    public static function looksLikeStun(string $d): bool
    {
        return \strlen($d) >= 20 && \ord($d[0]) < 4 && substr($d, 4, 4) === "\x21\x12\xA4\x42";
    }

    /** @return array{type: int, txid: string, attrs: array<int, string>, miOffset: int}|null */
    public static function parse(string $d): ?array
    {
        if (!self::looksLikeStun($d)) {
            return null;
        }
        $h = unpack('ntype/nlen', $d);
        if ($h === false) {
            return null;
        }
        $len = $h['len'];
        $end = 20 + $len;
        if ($len % 4 !== 0 || $end > \strlen($d)) {
            return null;
        }
        $attrs = [];
        $miOffset = -1;
        $off = 20;
        while ($off + 4 <= $end) {
            $a = unpack('ntype/nlen', $d, $off);
            if ($a === false || $off + 4 + $a['len'] > $end) {
                return null;
            }
            if ($a['type'] === self::ATTR_MESSAGE_INTEGRITY) {
                $miOffset = $off;
            }
            $attrs[$a['type']] = substr($d, $off + 4, $a['len']);
            $off += 4 + $a['len'] + ((4 - $a['len'] % 4) % 4);
        }
        return ['type' => $h['type'], 'txid' => substr($d, 8, 12), 'attrs' => $attrs, 'miOffset' => $miOffset];
    }

    /** @param array{attrs: array<int, string>, miOffset: int} $parsed */
    public static function verifyIntegrity(string $d, array $parsed, string $key): bool
    {
        $mi = $parsed['miOffset'];
        if ($mi < 20 || !isset($parsed['attrs'][self::ATTR_MESSAGE_INTEGRITY])) {
            return false;
        }
        $msg = substr($d, 0, 2) . pack('n', $mi - 20 + 24) . substr($d, 4, $mi - 4);
        return hash_equals(hash_hmac('sha1', $msg, $key, true), $parsed['attrs'][self::ATTR_MESSAGE_INTEGRITY]);
    }

    public static function buildSuccess(string $txid, string $ip, int $port, string $key): ?string
    {
        $ipLong = ip2long($ip);
        if ($ipLong === false) {
            return null;
        }
        $attrs = pack('nn', self::ATTR_XOR_MAPPED_ADDRESS, 8)
            . "\x00\x01"
            . pack('n', ($port ^ (self::MAGIC >> 16)) & 0xFFFF)
            . pack('N', ($ipLong ^ self::MAGIC) & 0xFFFFFFFF);

        $hdr = pack('nnN', self::BINDING_SUCCESS, \strlen($attrs) + 24, self::MAGIC) . $txid;
        $attrs .= pack('nn', self::ATTR_MESSAGE_INTEGRITY, 20) . hash_hmac('sha1', $hdr . $attrs, $key, true);

        $hdr = pack('nnN', self::BINDING_SUCCESS, \strlen($attrs) + 8, self::MAGIC) . $txid;
        $fp = (crc32($hdr . $attrs) ^ 0x5354554E) & 0xFFFFFFFF;
        $attrs .= pack('nnN', self::ATTR_FINGERPRINT, 4, $fp);

        return pack('nnN', self::BINDING_SUCCESS, \strlen($attrs), self::MAGIC) . $txid . $attrs;
    }
}
