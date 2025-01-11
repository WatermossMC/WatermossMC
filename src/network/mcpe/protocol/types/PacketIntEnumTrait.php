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

namespace watermossmc\network\mcpe\protocol\types;

use watermossmc\network\mcpe\protocol\PacketDecodeException;

/**
 * Trait for enums serialized in packets. Provides a convenient helper method to read, validate and properly bail on
 * invalid values.
 */
trait PacketIntEnumTrait
{
	/**
	 * @throws PacketDecodeException
	 */
	public static function fromPacket(int $value) : self
	{
		$enum = self::tryFrom($value);
		if ($enum === null) {
			throw new PacketDecodeException("Invalid raw value $value for " . static::class);
		}

		return $enum;
	}
}
