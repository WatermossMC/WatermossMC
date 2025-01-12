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

namespace watermossmc\world\format;

use pocketmine\world\format\LightArray as OriginalLightArray;

/**
 * Wrapper class for the LightArray class from the pocketmine\world\format namespace.
 */
class LightArray
{
	/** @var OriginalLightArray */
	private $lightArray;

	/**
	 * LightArray constructor.
	 *
	 * @param string $payload The payload to initialize the LightArray.
	 */
	public function __construct(string $payload)
	{
		// Create an instance of the original LightArray class
		$this->lightArray = new OriginalLightArray($payload);
	}

	/**
	 * Wraps the static `fill` method.
	 *
	 * @param int $level The light level to fill the array with.
	 *
	 * @return LightArray A new instance of the LightArray.
	 */
	public static function fill(int $level) : LightArray
	{
		$lightArray = OriginalLightArray::fill($level);
		return new LightArray($lightArray);
	}

	/**
	 * Wrapper for the `get` method.
	 *
	 * @param int $x The x-coordinate.
	 * @param int $y The y-coordinate.
	 * @param int $z The z-coordinate.
	 *
	 * @return int The light level at the specified coordinates.
	 */
	public function get(int $x, int $y, int $z) : int
	{
		return $this->lightArray->get($x, $y, $z);
	}

	/**
	 * Wrapper for the `set` method.
	 *
	 * @param int $x     The x-coordinate.
	 * @param int $y     The y-coordinate.
	 * @param int $z     The z-coordinate.
	 * @param int $level The light level to set at the specified coordinates.
	 */
	public function set(int $x, int $y, int $z, int $level) : void
	{
		$this->lightArray->set($x, $y, $z, $level);
	}

	/**
	 * Wrapper for the `getData` method.
	 *
	 * @return string The data associated with the LightArray.
	 */
	public function getData() : string
	{
		return $this->lightArray->getData();
	}

	/**
	 * Wrapper for the `collectGarbage` method.
	 *
	 * Collects unused memory or resources.
	 */
	public function collectGarbage() : void
	{
		$this->lightArray->collectGarbage();
	}

	/**
	 * Wrapper for the `isUniform` method.
	 *
	 * @param int $level The light level to check for uniformity.
	 *
	 * @return bool True if all blocks are uniform at the specified level, otherwise false.
	 */
	public function isUniform(int $level) : bool
	{
		return $this->lightArray->isUniform($level);
	}
}
