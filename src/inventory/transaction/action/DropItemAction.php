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

use watermossmc\event\player\PlayerDropItemEvent;
use watermossmc\inventory\transaction\TransactionValidationException;
use watermossmc\item\Item;
use watermossmc\item\VanillaItems;
use watermossmc\player\Player;

/**
 * Represents an action involving dropping an item into the world.
 */
class DropItemAction extends InventoryAction
{
	public function __construct(Item $targetItem)
	{
		parent::__construct(VanillaItems::AIR(), $targetItem);
	}

	public function validate(Player $source) : void
	{
		if ($this->targetItem->isNull()) {
			throw new TransactionValidationException("Cannot drop an empty itemstack");
		}
		if ($this->targetItem->getCount() > $this->targetItem->getMaxStackSize()) {
			throw new TransactionValidationException("Target item exceeds item type max stack size");
		}
	}

	public function onPreExecute(Player $source) : bool
	{
		$ev = new PlayerDropItemEvent($source, $this->targetItem);
		if ($source->isSpectator()) {
			$ev->cancel();
		}
		$ev->call();
		if ($ev->isCancelled()) {
			return false;
		}

		return true;
	}

	/**
	 * Drops the target item in front of the player.
	 */
	public function execute(Player $source) : void
	{
		$source->dropItem($this->targetItem);
	}
}
