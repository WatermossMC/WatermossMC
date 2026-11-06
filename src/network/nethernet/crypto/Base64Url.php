<?php

declare(strict_types=1);

namespace watermossmc\network\nethernet\crypto;

use function base64_decode;
use function base64_encode;
use function rtrim;
use function str_repeat;
use function strlen;
use function strtr;

/**
 * @internal
 */
final class Base64Url
{
    private function __construct()
    {
    }

    public static function encode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), "+/", "-_"), "=");
    }

    /**
     * @throws CryptoException
     */
    public static function decode(string $data): string
    {
        $remainder = strlen($data) % 4;
        if ($remainder !== 0) {
            $data .= str_repeat("=", 4 - $remainder);
        }

        $decoded = base64_decode(strtr($data, "-_", "+/"), true);
        if ($decoded === false) {
            throw new CryptoException("Value is not valid base64url");
        }

        return $decoded;
    }
}
