<?php

declare(strict_types=1);

namespace watermossmc\crypto;

use RuntimeException;

final class XboxAuth
{
    private const MOJANG_PUBLIC_KEY = "MHYwEAYHKoZIzj0CAQYFK4EEACIDYgAECRXueJeTDqNRRgJi/vlRufByu/2G0i2Ebt6YMar5QX/R0DIIyrJMcUpruK4QveTfJSTp3Shlq4Gk34cD/4GUWwkv0DVuzeuB+tXija7HBxii03NHDbPAD0AKnLr2wdAp";

    /**
     * Validates the JWT chain using Mojang's public key, verifying every
     * link's signature against the key advertised by the previous link.
     *
     * Bedrock chain structure:
     *   - Link 0: signed by Mojang root key; header x5u = Mojang key, payload identityPublicKey = next link's key
     *   - Link N: signed by previous link's identityPublicKey; payload identityPublicKey = client's public key
     *
     * @param array<mixed> $chain
     * @return array{data: array<string, mixed>, identityPublicKey: string}
     * @throws RuntimeException if the chain is malformed or any signature fails to verify
     */
    public static function validate(array $chain): array
    {
        // The first JWT must be signed by the Mojang root key.
        $currentKeyPem = Crypto::bedrockIdentityKeyToPem(self::MOJANG_PUBLIC_KEY);

        /** @var array<string, mixed> $data */
        $data = [];
        $identityPublicKey = null;

        foreach ($chain as $index => $jwt) {
            if (!\is_string($jwt)) {
                throw new RuntimeException("Chain entry #{$index} is not a string");
            }

            $parts = explode('.', $jwt);
            if (\count($parts) !== 3) {
                throw new RuntimeException("Malformed JWT at chain entry #{$index}");
            }

            [$headB64, $payloadB64, $sigB64] = $parts;

            $headerJson = self::urlSafeB64Decode($headB64);
            $payloadJson = self::urlSafeB64Decode($payloadB64);

            $header = json_decode($headerJson, true);
            $payload = json_decode($payloadJson, true);

            if (!\is_array($header) || !\is_array($payload)) {
                throw new RuntimeException("Failed to decode JWT JSON at chain entry #{$index}");
            }

            /** @var array<string, mixed> $header */
            /** @var array<string, mixed> $payload */

            $sigRaw = self::urlSafeB64Decode($sigB64);

            // Validate raw signature length: P-384 produces 96 bytes (2 × 48).
            if (\strlen($sigRaw) !== 96) {
                throw new RuntimeException(
                    "Unexpected signature length " . \strlen($sigRaw) . " at chain entry #{$index} (expected 96 for P-384)"
                );
            }

            $sigDer = self::signatureRawToDer($sigRaw);
            $contentToVerify = "$headB64.$payloadB64";

            // openssl_verify returns 1 (valid), 0 (invalid), or -1 (error).
            $verifyResult = openssl_verify($contentToVerify, $sigDer, $currentKeyPem, \OPENSSL_ALGO_SHA384);
            if ($verifyResult !== 1) {
                $opensslErr = openssl_error_string();
                throw new RuntimeException(
                    "Signature verification failed at chain entry #{$index}"
                    . ($opensslErr !== false ? ": $opensslErr" : "")
                );
            }

            // After verifying, advance the trust chain:
            // The next link must be signed by THIS link's identityPublicKey.
            if (isset($payload['identityPublicKey']) && \is_string($payload['identityPublicKey'])) {
                $identityPublicKey = $payload['identityPublicKey'];
                // Update the key used to verify the NEXT link in the chain.
                $currentKeyPem = Crypto::bedrockIdentityKeyToPem($identityPublicKey);
            }

            if (isset($payload['extraData']) && \is_array($payload['extraData'])) {
                /** @var array<string, mixed> $extraData */
                $extraData = $payload['extraData'];
                $data = array_merge($data, $extraData);
            }

            // Validate time claims if present.
            $now = time();
            if (isset($payload['nbf']) && \is_int($payload['nbf']) && $payload['nbf'] > $now + 60) {
                throw new RuntimeException("JWT chain entry #{$index} is not yet valid (nbf)");
            }
            if (isset($payload['exp']) && \is_int($payload['exp']) && $payload['exp'] < $now - 60) {
                throw new RuntimeException("JWT chain entry #{$index} has expired (exp)");
            }
        }

        if ($identityPublicKey === null) {
            throw new RuntimeException("Identity public key missing from chain");
        }

        return [
            'data' => $data,
            'identityPublicKey' => $identityPublicKey,
        ];
    }

    /**
     * Decodes client data JWT without signature verification (self-signed).
     *
     * @return array<string, mixed>
     */
    public static function decodeClientData(string $jwt): array
    {
        $parts = explode('.', $jwt);
        if (\count($parts) < 2) {
            return [];
        }

        $decoded = json_decode(self::urlSafeB64Decode($parts[1]), true);
        if (!\is_array($decoded)) {
            return [];
        }

        /** @var array<string, mixed> $decoded */
        return $decoded;
    }

    private static function urlSafeB64Decode(string $input): string
    {
        $remainder = \strlen($input) % 4;
        if ($remainder !== 0) {
            $input .= str_repeat('=', 4 - $remainder);
        }

        $decoded = base64_decode(strtr($input, '-_', '+/'), true);
        return \is_string($decoded) ? $decoded : '';
    }

    /**
     * Converts a raw ECDSA signature (IEEE P-1363 / raw r||s) to ASN.1 DER format.
     * For P-384 the input is always exactly 96 bytes (r=48, s=48).
     */
    private static function signatureRawToDer(string $raw): string
    {
        $half = intdiv(\strlen($raw), 2);
        $r = substr($raw, 0, $half);
        $s = substr($raw, $half);

        // Strip leading zero bytes, but keep at least one byte.
        $r = ltrim($r, "\x00");
        $s = ltrim($s, "\x00");

        // Prefix with 0x00 if high bit is set (to keep the value positive in DER).
        if ($r === '' || (\ord($r[0]) & 0x80)) {
            $r = "\x00" . $r;
        }
        if ($s === '' || (\ord($s[0]) & 0x80)) {
            $s = "\x00" . $s;
        }

        $rLen = \strlen($r);
        $sLen = \strlen($s);
        // Sequence length = INTEGER(r) + INTEGER(s) = (2 + rLen) + (2 + sLen)
        $seqLen = 4 + $rLen + $sLen;

        return "\x30" . \chr($seqLen)
            . "\x02" . \chr($rLen) . $r
            . "\x02" . \chr($sLen) . $s;
    }
}
