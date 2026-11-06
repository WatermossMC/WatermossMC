<?php

declare(strict_types=1);

namespace watermossmc\network\nethernet\webrtc;

use OpenSSLAsymmetricKey;
use RuntimeException;

final class LocalIdentity
{
    private const TOKEN_LIFETIME = 60;
    private const COORDINATE_SIZE = 48;

    private function __construct(
        private readonly OpenSSLAsymmetricKey $key,
        private readonly string $publicKeyBase64Der
    ) {
    }

    /** @param array<string, mixed> $opensslOptions */
    public static function generate(array $opensslOptions): self
    {
        $opts = $opensslOptions;
        $opts['private_key_type'] = \OPENSSL_KEYTYPE_EC;
        $opts['curve_name'] = 'secp384r1';
        $key = openssl_pkey_new($opts);
        $details = $key === false ? false : openssl_pkey_get_details($key);
        if ($key === false || $details === false || !isset($details['key'])) {
            throw new RuntimeException('P-384 identity key generation failed: ' . (string) openssl_error_string());
        }
        $b64 = (string) preg_replace('/-----[A-Z ]+-----|\s+/', '', $details['key']);
        return new self($key, $b64);
    }

    /**
     * Returns the a=identity attribute value (base64 JSON) for the local DTLS fingerprint
     * ("AA:BB:...").
     */
    public function issue(string $fingerprintDigest): string
    {
        $iat = time();
        $header = self::json(['x5u' => $this->publicKeyBase64Der, 'alg' => 'ES384']);
        $claims = self::json(['exp' => $iat + self::TOKEN_LIFETIME, 'iat' => $iat, 'cpk' => $this->publicKeyBase64Der]);
        $signingInput = self::b64u($header) . '.' . self::b64u($claims);
        $token = $signingInput . '.' . self::b64u($this->sign($signingInput));

        $protected = self::b64u('{"alg":"ES384"}');
        $payload = '{"fingerprint":[{"algorithm":"sha-256","digest":"' . $fingerprintDigest . '"}]}';
        $detached = $protected . '..' . self::b64u($this->sign($protected . '.' . self::b64u($payload)));

        $inner = self::json(['fingerprints' => $detached, 'token' => $token]);
        $outer = self::json(['assertion' => $inner, 'idp' => ['domain' => 'self', 'protocol' => 'default']]);
        return base64_encode($outer);
    }

    private function sign(string $input): string
    {
        $der = '';
        if (!openssl_sign($input, $der, $this->key, \OPENSSL_ALGO_SHA384)) {
            throw new RuntimeException('ES384 sign failed: ' . (string) openssl_error_string());
        }
        return self::derToJose($der);
    }

    private static function derToJose(string $der): string
    {
        if (\strlen($der) < 8 || $der[0] !== "\x30") {
            throw new RuntimeException('Bad ECDSA signature');
        }
        $o = 2;
        if ((\ord($der[1]) & 0x80) !== 0) {
            $o = 2 + (\ord($der[1]) & 0x7F);
        }
        $out = '';
        for ($i = 0; $i < 2; $i++) {
            if (!isset($der[$o + 1]) || $der[$o] !== "\x02") {
                throw new RuntimeException('Bad ECDSA signature');
            }
            $len = \ord($der[$o + 1]);
            $value = ltrim(substr($der, $o + 2, $len), "\0");
            $o += 2 + $len;
            $out .= str_pad($value, self::COORDINATE_SIZE, "\0", \STR_PAD_LEFT);
        }
        return $out;
    }

    private static function b64u(string $d): string
    {
        return rtrim(strtr(base64_encode($d), '+/', '-_'), '=');
    }

    /** @param array<string, mixed> $v */
    private static function json(array $v): string
    {
        return json_encode($v, \JSON_THROW_ON_ERROR | \JSON_UNESCAPED_SLASHES);
    }
}
