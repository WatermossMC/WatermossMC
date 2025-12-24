<?php

declare(strict_types=1);

namespace WatermossMC\Crypto;

use RuntimeException;

final class Crypto
{
    public static function generateKeyPair(): array
    {
        $res = openssl_pkey_new([
            'private_key_type' => \OPENSSL_KEYTYPE_EC,
            'curve_name' => 'prime256v1',
        ]);

        if ($res === false) {
            throw new RuntimeException("ECDH keygen failed");
        }

        openssl_pkey_export($res, $private);
        $details = openssl_pkey_get_details($res);

        return [
            'private' => $private,
            'public' => $details['key'], // PEM
        ];
    }

    public static function deriveSecret(string $serverPrivPem, string $clientPubPem): string
    {
        $priv = openssl_pkey_get_private($serverPrivPem);
        $pub = openssl_pkey_get_public($clientPubPem);

        if (!$priv || !$pub) {
            throw new RuntimeException("Invalid ECDH keys");
        }

        return openssl_pkey_derive($pub, $priv, 32);
    }

    public static function deriveAes(string $sharedSecret): array
    {
        $key = hash('sha256', $sharedSecret, true);
        $iv = substr(hash('sha256', "IV" . $sharedSecret, true), 0, 16);
        return [$key, $iv];
    }
}
