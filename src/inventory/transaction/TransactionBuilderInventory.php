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

namespace watermossmc\inventory\transaction;

use watermossmc\inventory\BaseInventory;
use watermossmc\inventory\Inventory;
use watermossmc\inventory\transaction\action\SlotChangeAction;
use watermossmc\item\Item;
use watermossmc\item\VanillaItems;

/**
 * This class facilitates generating SlotChangeActions to build an inventory transaction.
 * It wraps around the inventory you want to modify under transaction, and generates a diff of changes.
 * This allows you to use the normal Inventory API methods like addItem() and so on to build a transaction, without
 * modifying the original inventory.
 */
final class TransactionBuilderInventory extends BaseInventory
{
	/**
	 * @var \SplFixedArray|(Item|null)[]
	 * @phpstan-var \SplFixedArray<Item|null>
	 */
	private \SplFixedArray $changedSlots;

	public function __construct(
		private Inventory $actualInventory
	) {
		parent::__construct();
		$this->changedSlots = new \SplFixedArray($this->actualInventory->getSize());
	}

	public function getActualInventory() : Inventory
	{
		return $this->actualInventory;
	}

	protected function internalSetContents(array $items) : void
	{
		for ($i = 0, $size = $this->getSize(); $i < $size; ++$i) {
			if (!isset($items[$i])) {
				$this->clear($i);
			} else {
				$this->setItem($i, $items[$i]);
			}
		}
	}

	protected function internalSetItem(int $index, Item $item) : void
	{
		if (!$item->equalsExact($this->actualInventory->getItem($index))) {
			$this->changedSlots[$index] = $item->isNull() ? VanillaItems::AIR() : clone $item;
		}
	}

	public function getSize() : int
	{
		return $this->actualInventory->getSize();
	}

	public function getItem(int $index) : Item
	{
		return $this->changedSlots[$index] !== null ? clone $this->changedSlots[$index] : $this->actualInventory->getItem($index);
	}

	public function getContents(bool $includeEmpty = false) : array
	{
		$contents = $this->actualInventory->getContents($includeEmpty);
		foreach ($this->changedSlots as $index => $item) {
			if ($item !== null) {
				if ($includeEmpty || !$item->isNull()) {
					$contents[$index] = clone $item;
				} else {
					unset($contents[$index]);
				}
			}
		}
		return $contents;
	}

	/**
	 * @return SlotChangeAction[]
	 */
	public function generateActions() : array
	{
		$result = [];
		foreach ($this->changedSlots as $index => $newItem) {
			if ($newItem !== null) {
				$oldItem = $this->actualInventory->getItem($index);
				if (!$newItem->equalsExact($oldItem)) {
					$result[] = new SlotChangeAction($this->actualInventory, $index, $oldItem, $newItem);
				}
			}
		}
		return $result;
	}
}
