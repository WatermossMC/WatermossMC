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

namespace watermossmc\item\enchantment;

use DaveRandom\CallbackValidator\CallbackType;
use DaveRandom\CallbackValidator\ParameterType;
use DaveRandom\CallbackValidator\ReturnType;
use watermossmc\lang\Translatable;
use watermossmc\utils\NotCloneable;
use watermossmc\utils\NotSerializable;
use watermossmc\utils\Utils;

/**
 * Manages enchantment type data.
 */
class Enchantment
{
	use NotCloneable;
	use NotSerializable;

	/** @var \Closure(int $level) : int $minEnchantingPower */
	private \Closure $minEnchantingPower;

	/**
	 * @phpstan-param null|(\Closure(int $level) : int) $minEnchantingPower
	 *
	 * @param int $primaryItemFlags     @deprecated
	 * @param int $secondaryItemFlags   @deprecated
	 * @param int $enchantingPowerRange Value used to calculate the maximum enchanting power (minEnchantingPower + enchantingPowerRange)
	 */
	public function __construct(
		private Translatable|string $name,
		private int $rarity,
		private int $primaryItemFlags,
		private int $secondaryItemFlags,
		private int $maxLevel,
		?\Closure $minEnchantingPower = null,
		private int $enchantingPowerRange = 50
	) {
		$this->minEnchantingPower = $minEnchantingPower ?? fn (int $level) : int => 1;

		Utils::validateCallableSignature(new CallbackType(
			new ReturnType("int"),
			new ParameterType("level", "int")
		), $this->minEnchantingPower);
	}

	/**
	 * Returns a translation key for this enchantment's name.
	 */
	public function getName() : Translatable|string
	{
		return $this->name;
	}

	/**
	 * Returns an int constant indicating how rare this enchantment type is.
	 */
	public function getRarity() : int
	{
		return $this->rarity;
	}

	/**
	 * Returns a bitset indicating what item types can have this item applied from an enchanting table.
	 *
	 * @deprecated
	 * @see AvailableEnchantmentRegistry::getPrimaryItemTags()
	 */
	public function getPrimaryItemFlags() : int
	{
		return $this->primaryItemFlags;
	}

	/**
	 * Returns a bitset indicating what item types cannot have this item applied from an enchanting table, but can from
	 * an anvil.
	 *
	 * @deprecated
	 * @see AvailableEnchantmentRegistry::getSecondaryItemTags()
	 */
	public function getSecondaryItemFlags() : int
	{
		return $this->secondaryItemFlags;
	}

	/**
	 * Returns whether this enchantment can apply to the item type from an enchanting table.
	 *
	 * @deprecated
	 * @see AvailableEnchantmentRegistry
	 */
	public function hasPrimaryItemType(int $flag) : bool
	{
		return ($this->primaryItemFlags & $flag) !== 0;
	}

	/**
	 * Returns whether this enchantment can apply to the item type from an anvil, if it is not a primary item.
	 *
	 * @deprecated
	 * @see AvailableEnchantmentRegistry
	 */
	public function hasSecondaryItemType(int $flag) : bool
	{
		return ($this->secondaryItemFlags & $flag) !== 0;
	}

	/**
	 * Returns the maximum level of this enchantment that can be found on an enchantment table.
	 */
	public function getMaxLevel() : int
	{
		return $this->maxLevel;
	}

	/**
	 * Returns whether this enchantment can be applied to the item along with the given enchantment.
	 */
	public function isCompatibleWith(Enchantment $other) : bool
	{
		return IncompatibleEnchantmentRegistry::getInstance()->areCompatible($this, $other);
	}

	/**
	 * Returns the minimum enchanting power value required for the particular level of the enchantment
	 * to be available in an enchanting table.
	 *
	 * Enchanting power is a random value based on the number of bookshelves around an enchanting table
	 * and the enchantability of the item being enchanted. It is only used when determining the available
	 * enchantments for the enchantment options.
	 */
	public function getMinEnchantingPower(int $level) : int
	{
		return ($this->minEnchantingPower)($level);
	}

	/**
	 * Returns the maximum enchanting power value allowed for the particular level of the enchantment
	 * to be available in an enchanting table.
	 *
	 * Enchanting power is a random value based on the number of bookshelves around an enchanting table
	 * and the enchantability of the item being enchanted. It is only used when determining the available
	 * enchantments for the enchantment options.
	 */
	public function getMaxEnchantingPower(int $level) : int
	{
		return $this->getMinEnchantingPower($level) + $this->enchantingPowerRange;
	}
}
