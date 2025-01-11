<?php

/*
 *
 * This file part of WatermossMC.
 *
 *  __        __    _                                    __  __  ____
 *  \ \      / /_ _| |_ ___ _ __ _ __ ___   ___  ___ ___|  \/  |/ ___|
 *   \ \ /\ / / _` | __/ _ \ '__| '_ ` _ \ / _ \/ __/ __| |\/| | |
 *    \ V  V / (_| | ||  __/ |  | | | | | | (_) \__ \__ \ |  | | |___
 *     \_/\_/ \__,_|\__\___|_|  |_| |_| |_|\___/|___/___/_|  |_|\____|
 *
 * @author WatermossMC Team
 * @license Apache 2.0
 */

declare(strict_types=1);

namespace watermossmc\network\mcpe\encryption;

use Crypto\Cipher;
use watermossmc\utils\Binary;

use function bin2hex;
use function openssl_digest;
use function openssl_error_string;
use function strlen;
use function substr;

class EncryptionContext
{
	private const CHECKSUM_ALGO = "sha256";

	public static bool $ENABLED = true;

	private string $key;

	private Cipher $decryptCipher;
	private int $decryptCounter = 0;

	private Cipher $encryptCipher;
	private int $encryptCounter = 0;

	public function __construct(string $encryptionKey, string $algorithm, string $iv)
	{
		$this->key = $encryptionKey;

		$this->decryptCipher = new Cipher($algorithm);
		$this->decryptCipher->decryptInit($this->key, $iv);

		$this->encryptCipher = new Cipher($algorithm);
		$this->encryptCipher->encryptInit($this->key, $iv);
	}

	/**
	 * Returns an EncryptionContext suitable for decrypting Minecraft packets from 1.16.220.50 (protocol version 429) and up.
	 *
	 * MCPE uses GCM, but without the auth tag, which defeats the whole purpose of using GCM.
	 * GCM is just a wrapper around CTR which adds the auth tag, so CTR can replace GCM for this case.
	 * However, since GCM passes only the first 12 bytes of the IV followed by 0002, we must do the same for
	 * compatibility with MCPE.
	 * In PM, we could skip this and just use GCM directly (since we use OpenSSL), but this way is more portable, and
	 * better for developers who come digging in the PM code looking for answers.
	 */
	public static function fakeGCM(string $encryptionKey) : self
	{
		return new EncryptionContext(
			$encryptionKey,
			"AES-256-CTR",
			substr($encryptionKey, 0, 12) . "\x00\x00\x00\x02"
		);
	}

	public static function cfb8(string $encryptionKey) : self
	{
		return new EncryptionContext(
			$encryptionKey,
			"AES-256-CFB8",
			substr($encryptionKey, 0, 16)
		);
	}

	/**
	 * @throws DecryptionException
	 */
	public function decrypt(string $encrypted) : string
	{
		if (strlen($encrypted) < 9) {
			throw new DecryptionException("Payload is too short");
		}
		$decrypted = $this->decryptCipher->decryptUpdate($encrypted);
		$payload = substr($decrypted, 0, -8);

		$packetCounter = $this->decryptCounter++;

		if (($expected = $this->calculateChecksum($packetCounter, $payload)) !== ($actual = substr($decrypted, -8))) {
			throw new DecryptionException("Encrypted packet $packetCounter has invalid checksum (expected " . bin2hex($expected) . ", got " . bin2hex($actual) . ")");
		}

		return $payload;
	}

	public function encrypt(string $payload) : string
	{
		return $this->encryptCipher->encryptUpdate($payload . $this->calculateChecksum($this->encryptCounter++, $payload));
	}

	private function calculateChecksum(int $counter, string $payload) : string
	{
		$hash = openssl_digest(Binary::writeLLong($counter) . $payload . $this->key, self::CHECKSUM_ALGO, true);
		if ($hash === false) {
			throw new \RuntimeException("openssl_digest() error: " . openssl_error_string());
		}
		return substr($hash, 0, 8);
	}
}
