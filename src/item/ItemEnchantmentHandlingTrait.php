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

namespace watermossmc\item;

use watermossmc\item\enchantment\Enchantment;
use watermossmc\item\enchantment\EnchantmentInstance;

use function count;
use function spl_object_id;

/**
 * This trait encapsulates all enchantment handling needed for itemstacks.
 * The primary purpose of this trait is providing scope isolation for the methods it contains.
 */
trait ItemEnchantmentHandlingTrait
{
	/**
	 * @var EnchantmentInstance[]
	 * @phpstan-var array<int, EnchantmentInstance>
	 */
	protected array $enchantments = [];

	public function hasEnchantments() : bool
	{
		return count($this->enchantments) > 0;
	}

	public function hasEnchantment(Enchantment $enchantment, int $level = -1) : bool
	{
		$id = spl_object_id($enchantment);
		return isset($this->enchantments[$id]) && ($level === -1 || $this->enchantments[$id]->getLevel() === $level);
	}

	public function getEnchantment(Enchantment $enchantment) : ?EnchantmentInstance
	{
		return $this->enchantments[spl_object_id($enchantment)] ?? null;
	}

	/**
	 * @return $this
	 */
	public function removeEnchantment(Enchantment $enchantment, int $level = -1) : self
	{
		$instance = $this->getEnchantment($enchantment);
		if ($instance !== null && ($level === -1 || $instance->getLevel() === $level)) {
			unset($this->enchantments[spl_object_id($enchantment)]);
		}

		return $this;
	}

	/**
	 * @return $this
	 */
	public function removeEnchantments() : self
	{
		$this->enchantments = [];
		return $this;
	}

	/**
	 * @return $this
	 */
	public function addEnchantment(EnchantmentInstance $enchantment) : self
	{
		$this->enchantments[spl_object_id($enchantment->getType())] = $enchantment;
		return $this;
	}

	/**
	 * @return EnchantmentInstance[]
	 * @phpstan-return array<int, EnchantmentInstance>
	 */
	public function getEnchantments() : array
	{
		return $this->enchantments;
	}

	/**
	 * Returns the level of the enchantment on this item with the specified ID, or 0 if the item does not have the
	 * enchantment.
	 */
	public function getEnchantmentLevel(Enchantment $enchantment) : int
	{
		return ($instance = $this->getEnchantment($enchantment)) !== null ? $instance->getLevel() : 0;
	}
}
