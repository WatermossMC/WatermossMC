<?php

declare(strict_types=1);

namespace WatermossMC\Crypto;

use RuntimeException;

final class EncryptionContext
{
    private string $key;
    private string $baseIv;
    private int $encryptionCounter = 0;
    private int $decryptionCounter = 0;
    private int $encryptByteOffset = 0;
    private int $decryptByteOffset = 0;

    private const ALGORITHM = 'aes-256-ctr';
    private const BLOCK_SIZE = 16;

    public function __construct(string $key)
    {
        $this->key    = $key;
        $this->baseIv = substr($key, 0, 12) . "\x00\x00\x00\x02";
    }

    public function encrypt(string $data): string
    {
        $checksum  = $this->computeChecksum($data, $this->encryptionCounter++);
        $plaintext = $data . $checksum;

        $ciphertext = $this->ctrProcess($plaintext, $this->encryptByteOffset);
        $this->encryptByteOffset += strlen($plaintext);

        return $ciphertext;
    }

    public function decrypt(string $data): string
    {
        $decrypted = $this->ctrProcess($data, $this->decryptByteOffset);
        $this->decryptByteOffset += strlen($data);

        $payload        = substr($decrypted, 0, -8);
        $clientChecksum = substr($decrypted, -8);
        $expected       = $this->computeChecksum($payload, $this->decryptionCounter);

        if (!hash_equals($expected, $clientChecksum)) {
            throw new RuntimeException(
                "Checksum mismatch! counter=" . $this->decryptionCounter .
                " expected=" . bin2hex($expected) .
                " actual=" . bin2hex($clientChecksum)
            );
        }

        $this->decryptionCounter++;
        return $payload;
    }

    /**
     * CTR mode encryption/decryption (symmetric).
     * Handles mid-block offsets by prepending dummy bytes.
     */
    private function ctrProcess(string $data, int $byteOffset): string
    {
        $blockOffset = intdiv($byteOffset, self::BLOCK_SIZE);
        $byteInBlock = $byteOffset % self::BLOCK_SIZE;

        $iv = $this->incrementIv($blockOffset);

        if ($byteInBlock > 0) {
            // Prepend dummy bytes to align to block boundary
            $prefix    = str_repeat("\x00", $byteInBlock);
            $result    = openssl_encrypt($prefix . $data, self::ALGORITHM, $this->key, OPENSSL_RAW_DATA, $iv);
            if ($result === false) {
                throw new RuntimeException("CTR process failed");
            }
            return substr($result, $byteInBlock);
        }

        $result = openssl_encrypt($data, self::ALGORITHM, $this->key, OPENSSL_RAW_DATA, $iv);
        if ($result === false) {
            throw new RuntimeException("CTR process failed");
        }
        return $result;
    }

    private function incrementIv(int $blockOffset): string
    {
        if ($blockOffset === 0) {
            return $this->baseIv;
        }

        $ivBytes = array_values(unpack('C*', $this->baseIv));

        $carry = $blockOffset;
        for ($i = 15; $i >= 0 && $carry > 0; $i--) {
            $sum       = $ivBytes[$i] + ($carry & 0xFF);
            $ivBytes[$i] = $sum & 0xFF;
            $carry     = ($carry >> 8) + ($sum >> 8);
        }

        return pack('C*', ...$ivBytes);
    }

    private function computeChecksum(string $payload, int $counter): string
    {
        $hash = hash_init('sha256');
        hash_update($hash, pack('P', $counter));
        hash_update($hash, $payload);
        hash_update($hash, $this->key);
        return substr(hash_final($hash, true), 0, 8);
    }
}