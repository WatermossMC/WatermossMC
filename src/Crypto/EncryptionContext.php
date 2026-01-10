<?php

declare(strict_types=1);

namespace WatermossMC\Crypto;

final class EncryptionContext
{
    private string $key;

    private string $encryptIv;

    private string $decryptIv;

    public function __construct(string $key, string $iv)
    {
        $this->key = $key;
        $this->encryptIv = $iv;
        $this->decryptIv = $iv;
    }

    public function encrypt(string $data): string
    {
        $result = openssl_encrypt($data, 'aes-256-ctr', $this->key, \OPENSSL_RAW_DATA, $this->encryptIv);
        $this->encryptIv = $this->updateIv($this->encryptIv, \strlen($data));

        return $result . str_repeat("\x00", 8);
    }

    public function decrypt(string $data): string
    {
        $payload = substr($data, 0, -8);

        $result = openssl_decrypt($payload, 'aes-256-ctr', $this->key, \OPENSSL_RAW_DATA, $this->decryptIv);
        $this->decryptIv = $this->updateIv($this->decryptIv, \strlen($payload));

        return $result;
    }

    private function updateIv(string $iv, int $bytesProcessed): string
    {
        $addedSteps = $bytesProcessed >> 4;
        $ivParts = unpack('N4', $iv);
        $ivParts[4] += $addedSteps;
        return pack('N4', $ivParts[1], $ivParts[2], $ivParts[3], $ivParts[4]);
    }
}
