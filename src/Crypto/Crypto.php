<?php

declare(strict_types=1);

namespace WatermossMC\Crypto;

use RuntimeException;

final class Crypto
{
    /**
     * @return array{private: string, public: string}
     */
    public static function generateKeyPair(): array
    {
        $res = openssl_pkey_new([
            'private_key_type' => \OPENSSL_KEYTYPE_EC,
            'curve_name' => 'secp384r1',
        ]);

        if ($res === false) {
            throw new RuntimeException("ECDH keygen failed");
        }

        openssl_pkey_export($res, $privatePem);
        $details = openssl_pkey_get_details($res);

        return [
            'private' => $privatePem,
            'public' => $details['key'],
        ];
    }

    /**
     * @param string $serverPrivatePem
     * @param string $clientPublicPem
     * @return string
     */
    public static function deriveSecret(string $serverPrivatePem, string $clientPublicPem): string
    {
        $priv = openssl_pkey_get_private($serverPrivatePem);
        $pub = openssl_pkey_get_public($clientPublicPem);

        if ($priv === false || $pub === false) {
            throw new RuntimeException("Invalid keys for ECDH");
        }

        $secret = openssl_pkey_derive($pub, $priv);
        if ($secret === false) {
            throw new RuntimeException("ECDH derive failed");
        }

        return str_pad($secret, 48, "\0", \STR_PAD_LEFT);
    }

    /**
     * @param string $sharedSecret
     * @param string $salt
     * @return array{0: string, 1: string}
     */
    public static function deriveAes(string $sharedSecret, string $salt): array
    {
        $key = hash('sha256', $salt . $sharedSecret, true);

        $iv = substr($key, 0, 16);

        return [$key, $iv];
    }


    /**
     * @param string $pem
     * @return string
     */
    public static function pemToBase64(string $pem): string
    {
        return str_replace(
            ["-----BEGIN PUBLIC KEY-----", "-----END PUBLIC KEY-----", "\n", "\r", " "],
            "",
            $pem
        );
    }

    /**
     * @param string $b64
     * @return string
     */
    public static function bedrockIdentityKeyToPem(string $b64): string
    {
        return "-----BEGIN PUBLIC KEY-----\n"
            . chunk_split($b64, 64, "\n")
            . "-----END PUBLIC KEY-----\n";
    }

    /**
     * @param string $der
     * @param int $keySize
     * @return string
     */
    public static function derToSignature(string $der, int $keySize): string
    {
        if (strlen($der) < 8 || \ord($der[0]) !== 0x30) {
            throw new RuntimeException("Invalid DER signature");
        }

        $offset = 2;
        $sig = "";

        for ($i = 0; $i < 2; $i++) {
            if (\ord($der[$offset++]) !== 0x02) throw new RuntimeException("Invalid DER tag");
            $len = \ord($der[$offset++]);
            $val = substr($der, $offset, $len);
            $offset += $len;
            $val = ltrim($val, "\0");
            $sig .= str_pad($val, $keySize, "\0", \STR_PAD_LEFT);
        }

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
