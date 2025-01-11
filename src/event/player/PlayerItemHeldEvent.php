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
use watermossmc\item\Item;
use watermossmc\player\Player;

/**
 * Called when a player's held item changes.
 * This could be because they selected a different hotbar slot, or because the item in the selected hotbar slot was
 * changed.
 */
class PlayerItemHeldEvent extends PlayerEvent implements Cancellable
{
	use CancellableTrait;

	public function __construct(
		Player $player,
		private Item $item,
		private int $hotbarSlot
	) {
		$this->player = $player;
	}

	/**
	 * Returns the hotbar slot the player is attempting to hold.
	 *
	 * NOTE: This event is called BEFORE the slot is equipped server-side. Setting the player's held item during this
	 * event will result in the **old** slot being changed, not this one.
	 *
	 * To change the item in the slot that the player is attempting to hold, set the slot that this function reports.
	 */
	public function getSlot() : int
	{
		return $this->hotbarSlot;
	}

	/**
	 * Returns the item in the slot that the player is trying to equip.
	 */
	public function getItem() : Item
	{
		return clone $this->item;
	}
}
