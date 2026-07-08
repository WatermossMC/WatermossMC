<?php

/*
 * __        __    _                                    __  __  ____
 * \ \      / /_ _| |_ ___ _ __ _ __ ___   ___  ___ ___|  \/  |/ ___|
 *  \ \ /\ / / _` | __/ _ \ '__| '_ ` _ \ / _ \/ __/ __| |\/| | |
 *   \ V  V / (_| | ||  __/ |  | | | | | | (_) \__ \__ \ |  | | |___
 *    \_/\_/ \__,_|\__\___|_|  |_| |_| |_|\___/|___/___/_|  |_|\____|
 *
 * WatermossMC
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author WatermossMC Team
 * @link https://github.com/watermossmc/WatermossMC
 */

declare(strict_types=1);

namespace watermossmc\crypto;

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
        $this->key = $key;
        $this->baseIv = substr($key, 0, 12) . "\x00\x00\x00\x02";
    }

    public function encrypt(string $data): string
    {
        $checksum = $this->computeChecksum($data, $this->encryptionCounter++);
        $plaintext = $data . $checksum;

        $ciphertext = $this->ctrProcess($plaintext, $this->encryptByteOffset);
        $this->encryptByteOffset += \strlen($plaintext);

        return $ciphertext;
    }

    public function decrypt(string $data): string
    {
        $decrypted = $this->ctrProcess($data, $this->decryptByteOffset);
        $this->decryptByteOffset += \strlen($data);

        $payload = substr($decrypted, 0, -8);
        $clientChecksum = substr($decrypted, -8);
        $expected = $this->computeChecksum($payload, $this->decryptionCounter);

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
            $prefix = str_repeat("\x00", $byteInBlock);
            $result = openssl_encrypt($prefix . $data, self::ALGORITHM, $this->key, \OPENSSL_RAW_DATA, $iv);
            if ($result === false) {
                throw new RuntimeException("CTR process failed");
            }
            return substr($result, $byteInBlock);
        }

        $result = openssl_encrypt($data, self::ALGORITHM, $this->key, \OPENSSL_RAW_DATA, $iv);
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

        $unpacked = unpack('C*', $this->baseIv);
        if ($unpacked === false) {
            throw new RuntimeException("Failed to unpack baseIv");
        }
        $ivBytes = array_values($unpacked);

        $carry = $blockOffset;
        for ($i = 15; $i >= 0 && $carry > 0; $i--) {
            $sum = $ivBytes[$i] + ($carry & 0xFF);
            $ivBytes[$i] = $sum & 0xFF;
            $carry = ($carry >> 8) + ($sum >> 8);
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
