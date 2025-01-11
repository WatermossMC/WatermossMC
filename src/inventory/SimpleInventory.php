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

namespace watermossmc\inventory;

use watermossmc\item\Item;
use watermossmc\item\VanillaItems;

/**
 * This class provides a complete implementation of a regular inventory.
 */
class SimpleInventory extends BaseInventory
{
	/**
	 * @var \SplFixedArray|(Item|null)[]
	 * @phpstan-var \SplFixedArray<Item|null>
	 */
	protected \SplFixedArray $slots;

	public function __construct(int $size)
	{
		$this->slots = new \SplFixedArray($size);
		parent::__construct();
	}

	/**
	 * Returns the size of the inventory.
	 */
	public function getSize() : int
	{
		return $this->slots->getSize();
	}

	public function getItem(int $index) : Item
	{
		return $this->slots[$index] !== null ? clone $this->slots[$index] : VanillaItems::AIR();
	}

	protected function internalSetItem(int $index, Item $item) : void
	{
		$this->slots[$index] = $item->isNull() ? null : $item;
	}

	/**
	 * @return Item[]
	 * @phpstan-return array<int, Item>
	 */
	public function getContents(bool $includeEmpty = false) : array
	{
		$contents = [];

		foreach ($this->slots as $i => $slot) {
			if ($slot !== null) {
				$contents[$i] = clone $slot;
			} elseif ($includeEmpty) {
				$contents[$i] = VanillaItems::AIR();
			}
		}

		return $contents;
	}

	protected function internalSetContents(array $items) : void
	{
		for ($i = 0, $size = $this->getSize(); $i < $size; ++$i) {
			if (!isset($items[$i]) || $items[$i]->isNull()) {
				$this->slots[$i] = null;
			} else {
				$this->slots[$i] = clone $items[$i];
			}
		}
	}

	protected function getMatchingItemCount(int $slot, Item $test, bool $checkTags) : int
	{
		$slotItem = $this->slots[$slot];
		return $slotItem !== null && $slotItem->equals($test, true, $checkTags) ? $slotItem->getCount() : 0;
	}

	public function isSlotEmpty(int $index) : bool
	{
		return $this->slots[$index] === null || $this->slots[$index]->isNull();
	}
}
