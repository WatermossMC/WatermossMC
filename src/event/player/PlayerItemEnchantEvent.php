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

namespace watermossmc\event\player;

use watermossmc\event\Cancellable;
use watermossmc\event\CancellableTrait;
use watermossmc\inventory\transaction\EnchantingTransaction;
use watermossmc\item\enchantment\EnchantingOption;
use watermossmc\item\Item;
use watermossmc\player\Player;

/**
 * Called when a player enchants an item using an enchanting table.
 */
class PlayerItemEnchantEvent extends PlayerEvent implements Cancellable
{
	use CancellableTrait;

	public function __construct(
		Player $player,
		private readonly EnchantingTransaction $transaction,
		private readonly EnchantingOption $option,
		private readonly Item $inputItem,
		private readonly Item $outputItem,
		private readonly int $cost
	) {
		$this->player = $player;
	}

	/**
	 * Returns the inventory transaction involved in this enchant event.
	 */
	public function getTransaction() : EnchantingTransaction
	{
		return $this->transaction;
	}

	/**
	 * Returns the enchantment option used.
	 */
	public function getOption() : EnchantingOption
	{
		return $this->option;
	}

	/**
	 * Returns the item to be enchanted.
	 */
	public function getInputItem() : Item
	{
		return clone $this->inputItem;
	}

	/**
	 * Returns the enchanted item.
	 */
	public function getOutputItem() : Item
	{
		return clone $this->outputItem;
	}

	/**
	 * Returns the number of XP levels and lapis that will be subtracted after enchanting
	 * if the player is not in creative mode.
	 */
	public function getCost() : int
	{
		return $this->cost;
	}
}
