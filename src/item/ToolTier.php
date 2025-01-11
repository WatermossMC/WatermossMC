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

use watermossmc\utils\LegacyEnumShimTrait;

/**
 * TODO: These tags need to be removed once we get rid of LegacyEnumShimTrait (PM6)
 *  These are retained for backwards compatibility only.
 *
 * @method static ToolTier DIAMOND()
 * @method static ToolTier GOLD()
 * @method static ToolTier IRON()
 * @method static ToolTier NETHERITE()
 * @method static ToolTier STONE()
 * @method static ToolTier WOOD()
 *
 * @phpstan-type TMetadata array{0: int, 1: int, 2: int, 3: int, 4: int}
 */
enum ToolTier
{
	use LegacyEnumShimTrait;

	case WOOD;
	case GOLD;
	case STONE;
	case IRON;
	case DIAMOND;
	case NETHERITE;

	/**
	 * This function exists only to permit the use of named arguments and to make the code easier to read in PhpStorm.
	 * @phpstan-return TMetadata
	 */
	private static function meta(int $harvestLevel, int $maxDurability, int $baseAttackPoints, int $baseEfficiency, int $enchantability) : array
	{
		return [$harvestLevel, $maxDurability, $baseAttackPoints, $baseEfficiency, $enchantability];
	}

	/**
	 * @phpstan-return TMetadata
	 */
	private function getMetadata() : array
	{
		return match($this) {
			self::WOOD => self::meta(1, 60, 5, 2, 15),
			self::GOLD => self::meta(2, 33, 5, 12, 22),
			self::STONE => self::meta(3, 132, 6, 4, 5),
			self::IRON => self::meta(4, 251, 7, 6, 14),
			self::DIAMOND => self::meta(5, 1562, 8, 8, 10),
			self::NETHERITE => self::meta(6, 2032, 9, 9, 15)
		};
	}

	public function getHarvestLevel() : int
	{
		return $this->getMetadata()[0];
	}

	public function getMaxDurability() : int
	{
		return $this->getMetadata()[1];
	}

	public function getBaseAttackPoints() : int
	{
		return $this->getMetadata()[2];
	}

	public function getBaseEfficiency() : int
	{
		return $this->getMetadata()[3];
	}

	/**
	 * Returns the value that defines how enchantable the item is.
	 *
	 * The higher an item's enchantability is, the more likely it will be to gain high-level enchantments
	 * or multiple enchantments upon being enchanted in an enchanting table.
	 */
	public function getEnchantability() : int
	{
		return $this->getMetadata()[4];
	}
}
