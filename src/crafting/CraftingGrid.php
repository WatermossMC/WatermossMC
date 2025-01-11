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

namespace watermossmc\crafting;

use watermossmc\inventory\SimpleInventory;
use watermossmc\item\Item;

use function max;
use function min;

use const PHP_INT_MAX;

abstract class CraftingGrid extends SimpleInventory
{
	public const SIZE_SMALL = 2;
	public const SIZE_BIG = 3;

	private ?int $startX = null;
	private ?int $xLen = null;
	private ?int $startY = null;
	private ?int $yLen = null;

	public function __construct(
		private int $gridWidth
	) {
		parent::__construct($this->getGridWidth() ** 2);
	}

	public function getGridWidth() : int
	{
		return $this->gridWidth;
	}

	public function setItem(int $index, Item $item) : void
	{
		parent::setItem($index, $item);
		$this->seekRecipeBounds();
	}

	private function seekRecipeBounds() : void
	{
		$minX = PHP_INT_MAX;
		$maxX = 0;

		$minY = PHP_INT_MAX;
		$maxY = 0;

		$empty = true;

		for ($y = 0; $y < $this->gridWidth; ++$y) {
			for ($x = 0; $x < $this->gridWidth; ++$x) {
				if (!$this->isSlotEmpty($y * $this->gridWidth + $x)) {
					$minX = min($minX, $x);
					$maxX = max($maxX, $x);

					$minY = min($minY, $y);
					$maxY = max($maxY, $y);

					$empty = false;
				}
			}
		}

		if (!$empty) {
			$this->startX = $minX;
			$this->xLen = $maxX - $minX + 1;
			$this->startY = $minY;
			$this->yLen = $maxY - $minY + 1;
		} else {
			$this->startX = $this->xLen = $this->startY = $this->yLen = null;
		}
	}

	/**
	 * Returns the item at offset x,y, offset by where the starts of the recipe rectangle are.
	 */
	public function getIngredient(int $x, int $y) : Item
	{
		if ($this->startX !== null && $this->startY !== null) {
			return $this->getItem(($y + $this->startY) * $this->gridWidth + ($x + $this->startX));
		}

		throw new \LogicException("No ingredients found in grid");
	}

	/**
	 * Returns the width of the recipe we're trying to craft, based on items currently in the grid.
	 */
	public function getRecipeWidth() : int
	{
		return $this->xLen ?? 0;
	}

	/**
	 * Returns the height of the recipe we're trying to craft, based on items currently in the grid.
	 */
	public function getRecipeHeight() : int
	{
		return $this->yLen ?? 0;
	}
}
