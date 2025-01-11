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

use watermossmc\inventory\transaction\InventoryTransaction;
use watermossmc\inventory\transaction\TransactionValidationException;
use watermossmc\item\Item;
use watermossmc\player\Player;

/**
 * Represents an action involving a change that applies in some way to an inventory or other item-source.
 */
abstract class InventoryAction
{
	public function __construct(
		protected Item $sourceItem,
		protected Item $targetItem
	) {
	}

	/**
	 * Returns the item that was present before the action took place.
	 */
	public function getSourceItem() : Item
	{
		return clone $this->sourceItem;
	}

	/**
	 * Returns the item that the action attempted to replace the source item with.
	 */
	public function getTargetItem() : Item
	{
		return clone $this->targetItem;
	}

	/**
	 * Returns whether this action is currently valid. This should perform any necessary sanity checks.
	 *
	 * @throws TransactionValidationException
	 */
	abstract public function validate(Player $source) : void;

	/**
	 * Called when the action is added to the specified InventoryTransaction.
	 * @deprecated
	 */
	public function onAddToTransaction(InventoryTransaction $transaction) : void
	{

	}

	/**
	 * Called by inventory transactions before any actions are processed. If this returns false, the transaction will
	 * be cancelled.
	 */
	public function onPreExecute(Player $source) : bool
	{
		return true;
	}

	/**
	 * Performs actions needed to complete the inventory-action server-side. This will only be called if the transaction
	 * which it is part of is considered valid.
	 */
	abstract public function execute(Player $source) : void;
}
