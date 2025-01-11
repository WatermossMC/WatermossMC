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

namespace watermossmc\inventory\transaction\action;

use watermossmc\inventory\Inventory;
use watermossmc\inventory\SlotValidatedInventory;
use watermossmc\inventory\transaction\TransactionValidationException;
use watermossmc\item\Item;
use watermossmc\player\Player;

/**
 * Represents an action causing a change in an inventory slot.
 */
class SlotChangeAction extends InventoryAction
{
	public function __construct(
		protected Inventory $inventory,
		private int $inventorySlot,
		Item $sourceItem,
		Item $targetItem
	) {
		parent::__construct($sourceItem, $targetItem);
	}

	/**
	 * Returns the inventory involved in this action.
	 */
	public function getInventory() : Inventory
	{
		return $this->inventory;
	}

	/**
	 * Returns the slot in the inventory which this action modified.
	 */
	public function getSlot() : int
	{
		return $this->inventorySlot;
	}

	/**
	 * Checks if the item in the inventory at the specified slot is the same as this action's source item.
	 *
	 * @throws TransactionValidationException
	 */
	public function validate(Player $source) : void
	{
		if (!$this->inventory->slotExists($this->inventorySlot)) {
			throw new TransactionValidationException("Slot does not exist");
		}
		if (!$this->inventory->getItem($this->inventorySlot)->equalsExact($this->sourceItem)) {
			throw new TransactionValidationException("Slot does not contain expected original item");
		}
		if ($this->targetItem->getCount() > $this->targetItem->getMaxStackSize()) {
			throw new TransactionValidationException("Target item exceeds item type max stack size");
		}
		if ($this->targetItem->getCount() > $this->inventory->getMaxStackSize()) {
			throw new TransactionValidationException("Target item exceeds inventory max stack size");
		}
		if ($this->inventory instanceof SlotValidatedInventory && !$this->targetItem->isNull()) {
			foreach ($this->inventory->getSlotValidators() as $validator) {
				$ret = $validator->validate($this->inventory, $this->targetItem, $this->inventorySlot);
				if ($ret !== null) {
					throw new TransactionValidationException("Target item is not accepted by the inventory at slot #" . $this->inventorySlot . ": " . $ret->getMessage(), 0, $ret);
				}
			}
		}
	}

	/**
	 * Sets the item into the target inventory.
	 */
	public function execute(Player $source) : void
	{
		$this->inventory->setItem($this->inventorySlot, $this->targetItem);
	}
}
