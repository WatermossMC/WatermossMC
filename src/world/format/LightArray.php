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

use pocketmine\world\format\\pocketmine\worldormat\LightArray as Original\pocketmine\worldormat\LightArray;

/**
 * Wrapper class for the \pocketmine\worldormat\LightArray class from the pocketmine\world\format namespace.
 */
class \pocketmine\worldormat\LightArray
{
	/** @var Original\pocketmine\worldormat\LightArray */
	private $\pocketmine\worldormat\LightArray;

	/**
	 * \pocketmine\worldormat\LightArray constructor.
	 *
	 * @param string $payload The payload to initialize the \pocketmine\worldormat\LightArray.
	 */
	public function __construct(string $payload)
	{
		// Create an instance of the original \pocketmine\worldormat\LightArray class
		$this->\pocketmine\worldormat\LightArray = new Original\pocketmine\worldormat\LightArray($payload);
	}

	/**
	 * Wraps the static `fill` method.
	 *
	 * @param int $level The light level to fill the array with.
	 *
	 * @return \pocketmine\worldormat\LightArray A new instance of the \pocketmine\worldormat\LightArray.
	 */
	public static function fill(int $level) : \pocketmine\worldormat\LightArray
	{
		$\pocketmine\worldormat\LightArray = Original\pocketmine\worldormat\LightArray::fill($level);
		return new \pocketmine\worldormat\LightArray($\pocketmine\worldormat\LightArray);
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
		return $this->\pocketmine\worldormat\LightArray->get($x, $y, $z);
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
		$this->\pocketmine\worldormat\LightArray->set($x, $y, $z, $level);
	}

	/**
	 * Sets all light levels in the array to the specified level.
	 *
	 * @param int $level The light level to set for all coordinates.
	 */
	public function setAll(int $level) : void
	{
		$this->\pocketmine\worldormat\LightArray->setAll($level);
	}

	/**
	 * Wrapper for the `getData` method.
	 *
	 * @return string The data associated with the \pocketmine\worldormat\LightArray.
	 */
	public function getData() : string
	{
		return $this->\pocketmine\worldormat\LightArray->getData();
	}

	/**
	 * Wrapper for the `collectGarbage` method.
	 *
	 * Collects unused memory or resources.
	 */
	public function collectGarbage() : void
	{
		$this->\pocketmine\worldormat\LightArray->collectGarbage();
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
		return $this->\pocketmine\worldormat\LightArray->isUniform($level);
	}
}
