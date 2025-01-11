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

use watermossmc\network\mcpe\JwtUtils;
use watermossmc\scheduler\AsyncTask;
use watermossmc\utils\AssumptionFailedError;

use function igbinary_serialize;
use function igbinary_unserialize;
use function openssl_error_string;
use function openssl_pkey_get_details;
use function openssl_pkey_new;
use function random_bytes;

class PrepareEncryptionTask extends AsyncTask
{
	private const TLS_KEY_ON_COMPLETION = "completion";

	private static ?\OpenSSLAsymmetricKey $SERVER_PRIVATE_KEY = null;

	private string $serverPrivateKey;

	private ?string $aesKey = null;
	private ?string $handshakeJwt = null;

	/**
	 * @phpstan-param \Closure(string $encryptionKey, string $handshakeJwt) : void $onCompletion
	 */
	public function __construct(
		private string $clientPub,
		\Closure $onCompletion
	) {
		if (self::$SERVER_PRIVATE_KEY === null) {
			$serverPrivateKey = openssl_pkey_new(["ec" => ["curve_name" => "secp384r1"]]);
			if ($serverPrivateKey === false) {
				throw new \RuntimeException("openssl_pkey_new() failed: " . openssl_error_string());
			}
			self::$SERVER_PRIVATE_KEY = $serverPrivateKey;
		}

		$this->serverPrivateKey = igbinary_serialize(openssl_pkey_get_details(self::$SERVER_PRIVATE_KEY));
		$this->storeLocal(self::TLS_KEY_ON_COMPLETION, $onCompletion);
	}

	public function onRun() : void
	{
		/** @var mixed[] $serverPrivDetails */
		$serverPrivDetails = igbinary_unserialize($this->serverPrivateKey);
		$serverPriv = openssl_pkey_new($serverPrivDetails);
		if ($serverPriv === false) {
			throw new AssumptionFailedError("Failed to restore server signing key from details");
		}
		$clientPub = JwtUtils::parseDerPublicKey($this->clientPub);
		$sharedSecret = EncryptionUtils::generateSharedSecret($serverPriv, $clientPub);

		$salt = random_bytes(16);
		$this->aesKey = EncryptionUtils::generateKey($sharedSecret, $salt);
		$this->handshakeJwt = EncryptionUtils::generateServerHandshakeJwt($serverPriv, $salt);
	}

	public function onCompletion() : void
	{
		/**
		 * @var \Closure $callback
		 * @phpstan-var \Closure(string $encryptionKey, string $handshakeJwt) : void $callback
		 */
		$callback = $this->fetchLocal(self::TLS_KEY_ON_COMPLETION);
		if ($this->aesKey === null || $this->handshakeJwt === null) {
			throw new AssumptionFailedError("Something strange happened here ...");
		}
		$callback($this->aesKey, $this->handshakeJwt);
	}
}
