<?php

declare(strict_types=1);

namespace watermossmc\network\nethernet\crypto;

use function openssl_error_string;

/**
 * @internal
 */
final class OpenSsl
{
    private function __construct()
    {
    }

    public static function lastError(): string
    {
        $message = null;
        while (($error = openssl_error_string()) !== false) {
            $message = $error;
        }

        return $message ?? "no detail available";
    }
}
