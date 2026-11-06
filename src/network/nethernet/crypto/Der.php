<?php

declare(strict_types=1);

namespace watermossmc\network\nethernet\crypto;

use function chr;
use function ltrim;
use function pack;
use function strlen;

/**
 * @internal
 */
final class Der
{
    private const MAX_LENGTH = 0xffffffff;

    private function __construct()
    {
    }

    public static function encode(int $tag, string $body): string
    {
        return chr($tag) . self::length(strlen($body)) . $body;
    }

    public static function sequence(string $body): string
    {
        return self::encode(0x30, $body);
    }

    public static function integer(string $body): string
    {
        return self::encode(0x02, $body);
    }

    public static function unsignedInteger(string $value): string
    {
        $value = ltrim($value, "\x00");
        if ($value === "" || ($value[0] & "\x80") !== "\x00") {
            $value = "\x00" . $value;
        }

        return self::integer($value);
    }

    public static function octetString(string $body): string
    {
        return self::encode(0x04, $body);
    }

    public static function bitString(string $body): string
    {
        return self::encode(0x03, "\x00" . $body);
    }

    public static function tagged(int $number, string $body): string
    {
        return self::encode(0xa0 | $number, $body);
    }

    private static function length(int $length): string
    {
        if ($length <= 0x7f) {
            return chr($length);
        }
        if ($length > self::MAX_LENGTH) {
            throw new \InvalidArgumentException("DER body of $length bytes is too long to encode");
        }

        $bytes = ltrim(pack("N", $length), "\x00");

        return chr(0x80 | strlen($bytes)) . $bytes;
    }
}
