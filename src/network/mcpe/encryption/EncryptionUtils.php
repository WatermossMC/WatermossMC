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
use watermossmc\utils\Utils;

use function base64_encode;
use function bin2hex;
use function gmp_init;
use function gmp_strval;
use function hex2bin;
use function openssl_digest;
use function openssl_error_string;
use function openssl_pkey_derive;
use function openssl_pkey_get_details;
use function str_pad;

use const STR_PAD_LEFT;

final class EncryptionUtils
{
	private function __construct()
	{
		//NOOP
	}

	private static function validateKey(\OpenSSLAsymmetricKey $key) : void
	{
		$keyDetails = Utils::assumeNotFalse(openssl_pkey_get_details($key));
		if (!isset($keyDetails["ec"]["curve_name"])) {
			throw new \InvalidArgumentException("Key must be an EC key");
		}
		$curveName = $keyDetails["ec"]["curve_name"];
		if ($curveName !== JwtUtils::BEDROCK_SIGNING_KEY_CURVE_NAME) {
			throw new \InvalidArgumentException("Key must belong to the " . JwtUtils::BEDROCK_SIGNING_KEY_CURVE_NAME . " elliptic curve, got $curveName");
		}
	}

	public static function generateSharedSecret(\OpenSSLAsymmetricKey $localPriv, \OpenSSLAsymmetricKey $remotePub) : \GMP
	{
		self::validateKey($localPriv);
		self::validateKey($remotePub);
		$hexSecret = openssl_pkey_derive($remotePub, $localPriv, 48);
		if ($hexSecret === false) {
			throw new \InvalidArgumentException("Failed to derive shared secret: " . openssl_error_string());
		}
		return gmp_init(bin2hex($hexSecret), 16);
	}

	public static function generateKey(\GMP $secret, string $salt) : string
	{
		return Utils::assumeNotFalse(openssl_digest($salt . hex2bin(str_pad(gmp_strval($secret, 16), 96, "0", STR_PAD_LEFT)), 'sha256', true));
	}

	public static function generateServerHandshakeJwt(\OpenSSLAsymmetricKey $serverPriv, string $salt) : string
	{
		$derPublicKey = JwtUtils::emitDerPublicKey($serverPriv);
		return JwtUtils::create(
			[
				"x5u" => base64_encode($derPublicKey),
				"alg" => "ES384"
			],
			[
				"salt" => base64_encode($salt)
			],
			$serverPriv
		);
	}
}
