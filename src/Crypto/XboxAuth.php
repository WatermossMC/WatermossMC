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
     * @param array<mixed> $chain
     * @return array{data: array<string, mixed>, identityPublicKey: string}
     * @throws RuntimeException
     */
    public static function validate(array $chain): array
    {
        $currentKey = Crypto::bedrockIdentityKeyToPem(self::MOJANG_PUBLIC_KEY);
        
        /** @var array<string, mixed> $data */
        $data = [];
        $identityPublicKey = null;
        $valid = false;

        foreach ($chain as $jwt) {
            if (!is_string($jwt)) {
                continue;
            }

            $parts = explode('.', $jwt);
            if (count($parts) !== 3) {
                continue;
            }

            [$headB64, $payloadB64, $sigB64] = $parts;

            $header = json_decode(self::urlSafeB64Decode($headB64), true);
            $payload = json_decode(self::urlSafeB64Decode($payloadB64), true);
            
            if (!is_array($header) || !is_array($payload)) {
                throw new RuntimeException("Failed to decode JWT JSON");
            }

            /** @var array<string, mixed> $header */
            /** @var array<string, mixed> $payload */

            $sigRaw = self::urlSafeB64Decode($sigB64);
            $sigDer = self::signatureRawToDer($sigRaw);
            
            $contentToVerify = "$headB64.$payloadB64";
            
            openssl_verify($contentToVerify, $sigDer, $currentKey, "sha384");

            if (isset($header['x5u']) && is_string($header['x5u'])) {
                $currentKey = Crypto::bedrockIdentityKeyToPem($header['x5u']);
                $valid = true;
            }

            if (isset($payload['extraData']) && is_array($payload['extraData'])) {
                /** @var array<string, mixed> $extraData */
                $extraData = $payload['extraData'];
                $data = array_merge($data, $extraData);
            }
            if (isset($payload['identityPublicKey']) && is_string($payload['identityPublicKey'])) {
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
    /**
     * @param string $jwt
     * @return array<string, mixed>
     */
    public static function decodeClientData(string $jwt): array
    {
        $parts = explode('.', $jwt);
        if (count($parts) < 2) {
            return [];
        }

        $decoded = json_decode(self::urlSafeB64Decode($parts[1]), true);
        if (!is_array($decoded)) {
            return [];
        }

        /** @var array<string, mixed> $decoded */
        return $decoded;
    }

    private static function urlSafeB64Decode(string $input): string
    {
        $remainder = strlen($input) % 4;
        if ($remainder) {
            $padLength = 4 - $remainder;
            $input .= str_repeat('=', $padLength);
        }

        $decoded = base64_decode(strtr($input, '-_', '+/'), true);
        return is_string($decoded) ? $decoded : '';
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
        $half = intdiv($len, 2);
        $r = substr($raw, 0, $half);
        $s = substr($raw, $half);

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
