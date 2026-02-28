<?php

declare(strict_types=1);

namespace WatermossMC\Crypto;

use RuntimeException;

final class EncryptionContext
{
    private string $key;
    private string $iv;
    private int $encryptionCounter = 0;
    private int $decryptionCounter = 0;

    private const ALGORITHM = 'aes-256-cfb8';

    public function __construct(string $key, string $iv)
    {
        $this->key = $key;
        $this->iv = $iv;
    }

    public function encrypt(string $data): string
    {
        $checksum = $this->computeChecksum($data, $this->encryptionCounter++);
        $payloadWithChecksum = $data . $checksum;

        $ciphertext = openssl_encrypt(
            $payloadWithChecksum,
            self::ALGORITHM,
            $this->key,
            \OPENSSL_RAW_DATA,
            $this->iv
        );

        if ($ciphertext === false) {
            throw new RuntimeException("CFB8 encryption failed");
        }

        $this->iv = substr($this->iv . $ciphertext, -16); 

        return $ciphertext;
    }

    public function decrypt(string $data): string
    {
        $decrypted = openssl_decrypt(
            $data,
            self::ALGORITHM,
            $this->key,
            \OPENSSL_RAW_DATA,
            $this->iv
        );

        if ($decrypted === false) {
            throw new RuntimeException("CFB8 decryption failed");
        }

        $this->iv = substr($this->iv . $data, -16);

        $payload = substr($decrypted, 0, -8);
        $clientChecksum = substr($decrypted, -8);

        $expected = $this->computeChecksum($payload, $this->decryptionCounter++);

        if (!hash_equals($expected, $clientChecksum)) {
            throw new RuntimeException("Checksum mismatch! Possible out of sync.");
        }

        return $payload;
    }

    private function computeChecksum(string $payload, int $counter): string
    {
        $hash = hash_init('sha256');

        hash_update($hash, pack("P", $counter)); 
        hash_update($hash, $payload);
        hash_update($hash, $this->key);
        
        return substr(hash_final($hash, true), 0, 8);
    }
}
