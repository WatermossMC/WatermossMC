<?php

declare(strict_types=1);

namespace WatermossMC\Crypto;

use RuntimeException;

final class Crypto
{
    /**
     * Generate EC key pair (P-384) for Bedrock ECDH
     * @return array{private: string, public: string}
     */
    public static function generateKeyPair(): array
    {
        $res = openssl_pkey_new([
            'private_key_type' => \OPENSSL_KEYTYPE_EC,
            'curve_name' => 'secp384r1',
        ]);

        if ($res === false) {
            throw new RuntimeException("ECDH keygen failed: " . openssl_error_string());
        }

        openssl_pkey_export($res, $privatePem);
        $details = openssl_pkey_get_details($res);

        if (!isset($details['key'])) {
            throw new RuntimeException("Failed extracting public key");
        }

        return [
            'private' => $privatePem,
            'public' => $details['key'], // PEM format
        ];
    }

    /**
     * Derive shared secret using ECDH
     */
    public static function deriveSecret(string $serverPrivatePem, string $clientPublicPem): string
    {
        $priv = openssl_pkey_get_private($serverPrivatePem);
        if ($priv === false) {
            throw new RuntimeException("Invalid server private key format for ECDH");
        }
        $pub = openssl_pkey_get_public($clientPublicPem);

        if ($pub === false) {
            throw new RuntimeException("Invalid client public key format for ECDH");
        }

        $secret = openssl_pkey_derive($pub, $priv);

        if ($secret === false) {
            throw new RuntimeException("ECDH derive failed");
        }

        // Shared Secret P-384 biasanya 48 bytes, tapi jika leading byte 0, PHP mungkin memotongnya.
        // Kita tidak boleh padding manual dengan \0 di kiri sembarangan kecuali kita yakin panjangnya kurang.
        // Bedrock menggunakan koordinat X dari hasil ECDH.
        return str_pad($secret, 48, "\0", \STR_PAD_LEFT);
    }

    /**
     * @return array{0: string, 1: string}
     */
    public static function deriveAes(string $sharedSecret, string $salt): array
    {
        $key = hash('sha256', $salt . $sharedSecret, true);

        $ivHash = hash('sha256', $salt . $sharedSecret . $salt, true);
        $iv = substr($ivHash, 0, 16);

        return [$key, $iv];
    }

    public static function pemToBase64(string $pem): string
    {
        return str_replace(
            ["-----BEGIN PUBLIC KEY-----", "-----END PUBLIC KEY-----", "\n", "\r", " "],
            "",
            $pem
        );
    }

    public static function bedrockIdentityKeyToPem(string $b64): string
    {
        $der = base64_decode($b64, true);
        if ($der === false) {
            throw new RuntimeException("Invalid base64 identityPublicKey");
        }

        return "-----BEGIN PUBLIC KEY-----\n"
            . chunk_split(base64_encode($der), 64, "\n")
            . "-----END PUBLIC KEY-----\n";
    }

    public static function derToSignature(string $der, int $keySize): string
    {
        // DER SEQUENCE format: 30 [length] 02 [r_len] [r_bytes] 02 [s_len] [s_bytes]
        if (strlen($der) < 8 || \ord($der[0]) !== 0x30) {
            throw new RuntimeException("Invalid DER signature format");
        }

        $offset = 2;
        $sig = "";

        // Parse r
        if (\ord($der[$offset]) !== 0x02) {
            throw new RuntimeException("Invalid DER r tag");
        }
        $offset++;
        $rLen = \ord($der[$offset++]);
        $r = substr($der, $offset, $rLen);
        $offset += $rLen;
        $r = ltrim($r, "\0");
        $sig .= str_pad($r, $keySize, "\0", \STR_PAD_LEFT);

        // Parse s
        if (\ord($der[$offset]) !== 0x02) {
            throw new RuntimeException("Invalid DER s tag");
        }
        $offset++;
        $sLen = \ord($der[$offset++]);
        $s = substr($der, $offset, $sLen);
        $offset += $sLen;
        $s = ltrim($s, "\0");
        $sig .= str_pad($s, $keySize, "\0", \STR_PAD_LEFT);

        return $sig;
    }

    public static function signatureToDer(string $sigRaw): string
    {
        $len = strlen($sigRaw);
        if ($len % 2 !== 0) {
            throw new RuntimeException('Invalid raw signature length');
        }
        $half = $len / 2;
        $r = substr($sigRaw, 0, $half);
        $s = substr($sigRaw, $half);

        $r = ltrim($r, "\0");
        if ($r === '') {
            $r = "\0";
        }
        if ((ord($r[0]) & 0x80) !== 0) {
            $r = "\0" . $r;
        }

        $s = ltrim($s, "\0");
        if ($s === '') {
            $s = "\0";
        }
        if ((ord($s[0]) & 0x80) !== 0) {
            $s = "\0" . $s;
        }

        $derR = "\x02" . chr(strlen($r)) . $r;
        $derS = "\x02" . chr(strlen($s)) . $s;
        $seq = $derR . $derS;
        return "\x30" . chr(strlen($seq)) . $seq;
    }
}
