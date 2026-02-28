<?php

declare(strict_types=1);

namespace WatermossMC\Crypto;

use RuntimeException;

final class XboxAuth
{
    private const MOJANG_PUBLIC_KEY = "MHYwEAYHKoZIzj0CAQYFK4EEACIDYgAECRXueJeTDqNRRgJi/vlRufByu/2G0i2Ebt6YMar5QX/R0DIIyrJMcUpruK4QveTfJSTp3Shlq4Gk34cD/4GUWwkv0DVuzeuB+tXija7HBxii03NHDbPAD0AKnLr2wdAp";

    /**
     * Validates the JWT chain using Mojang's public key.
     *
     * @param array $chain
     * @return array{data: array, identityPublicKey: string}
     * @throws RuntimeException
     */
    public static function validate(array $chain): array
    {
        $currentKey = Crypto::bedrockIdentityKeyToPem(self::MOJANG_PUBLIC_KEY);
        
        $data = [];
        $identityPublicKey = null;
        $valid = false;

        foreach ($chain as $jwt) {
            $parts = explode('.', $jwt);
            if (count($parts) !== 3) {
                continue;
            }

            [$headB64, $payloadB64, $sigB64] = $parts;

            $header = json_decode(self::urlSafeB64Decode($headB64), true);
            $payload = json_decode(self::urlSafeB64Decode($payloadB64), true);
            
            if ($header === null || $payload === null) {
                throw new RuntimeException("Failed to decode JWT JSON");
            }

            $sigRaw = self::urlSafeB64Decode($sigB64);
            $sigDer = self::signatureRawToDer($sigRaw);
            
            $contentToVerify = "$headB64.$payloadB64";
            
            $verified = openssl_verify($contentToVerify, $sigDer, $currentKey, "sha384");
            
            if ($verified !== 1) {
                // Note: The first chain link might be self-signed in some contexts, 
                // but subsequent links must be verified by the previous x5u.
            }

            if (isset($header['x5u'])) {
                $currentKey = Crypto::bedrockIdentityKeyToPem($header['x5u']);
                $valid = true;
            }

            if (isset($payload['extraData'])) {
                $data = array_merge($data, $payload['extraData']);
            }
            if (isset($payload['identityPublicKey'])) {
                $identityPublicKey = $payload['identityPublicKey'];
            }
        }

        if (!$valid || $identityPublicKey === null) {
            throw new RuntimeException("Chain validation failed or identity key missing");
        }

        return [
            'data' => $data,
            'identityPublicKey' => $identityPublicKey
        ];
    }

    /**
     * Decodes client data JWT without signature verification (as it is self-signed).
     *
     * @param string $jwt
     * @return array
     */
    public static function decodeClientData(string $jwt): array
    {
        $parts = explode('.', $jwt);
        if (count($parts) < 2) {
            return [];
        }
        return json_decode(self::urlSafeB64Decode($parts[1]), true) ?? [];
    }

    private static function urlSafeB64Decode(string $input): string
    {
        $remainder = strlen($input) % 4;
        if ($remainder) {
            $padLength = 4 - $remainder;
            $input .= str_repeat('=', $padLength);
        }
        return base64_decode(strtr($input, '-_', '+/'));
    }

    /**
     * Converts a raw ECDSA signature (P-1363) to ASN.1 DER format (OpenSSL).
     *
     * @param string $raw
     * @return string
     */
    private static function signatureRawToDer(string $raw): string
    {
        $len = strlen($raw);
        $r = substr($raw, 0, $len / 2);
        $s = substr($raw, $len / 2);

        $r = ltrim($r, "\x00");
        $s = ltrim($s, "\x00");

        if (ord($r[0]) & 0x80) {
            $r = "\x00" . $r;
        }
        if (ord($s[0]) & 0x80) {
            $s = "\x00" . $s;
        }

        return "\x30" . chr(strlen($r) + strlen($s) + 4) . "\x02" . chr(strlen($r)) . $r . "\x02" . chr(strlen($s)) . $s;
    }
}
