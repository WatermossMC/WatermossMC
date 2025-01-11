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

namespace watermossmc\network\mcpe\protocol\types\login;

/**
 * Model class for JsonMapper which describes the RFC7519 standard fields in a JWT. Any of these fields might not be
 * provided.
 */
class JwtBodyRfc7519
{
	public string $iss;
	public string $sub;
	/** @var string|string[] */
	public $aud;
	public int $exp;
	public int $nbf;
	public int $iat;
	public string $jti;
}
