<?php

declare(strict_types=1);

namespace WatermossMC\Crypto;

final class EncryptionContext
{
    private string $key;

    private string $baseIv;

    private int $sendCounter = 0;

    private int $recvCounter = 0;

    public function __construct(string $key, string $iv)
    {
        $this->key = $key;
        $this->baseIv = $iv;
    }

    private function makeIv(int $counter): string
    {
        return substr($this->baseIv, 0, 12) . pack("N", $counter);
    }

    public function encrypt(string $data): string
    {
        $iv = $this->makeIv($this->sendCounter++);
        return openssl_encrypt(
            $data,
            'aes-256-ctr',
            $this->key,
            \OPENSSL_RAW_DATA,
            $iv
        );
    }

    public function decrypt(string $data): string
    {
        $iv = $this->makeIv($this->recvCounter++);
        return openssl_decrypt(
            $data,
            'aes-256-ctr',
            $this->key,
            \OPENSSL_RAW_DATA,
            $iv
        );
    }
}
