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

namespace watermossmc\event\block;

use watermossmc\block\Block;
use watermossmc\event\Cancellable;
use watermossmc\event\CancellableTrait;
use watermossmc\event\Event;
use watermossmc\item\Item;
use watermossmc\player\Player;
use watermossmc\world\BlockTransaction;

/**
 * Called when a player initiates a block placement action.
 * More than one block may be changed by a single placement action, for example when placing a door.
 */
class BlockPlaceEvent extends Event implements Cancellable
{
	use CancellableTrait;

	public function __construct(
		protected Player $player,
		protected BlockTransaction $transaction,
		protected Block $blockAgainst,
		protected Item $item
	) {
		$world = $this->blockAgainst->getPosition()->getWorld();
		foreach ($this->transaction->getBlocks() as [$x, $y, $z, $block]) {
			$block->position($world, $x, $y, $z);
		}
	}

	/**
	 * Returns the player who is placing the block.
	 */
	public function getPlayer() : Player
	{
		return $this->player;
	}

	/**
	 * Gets the item in hand
	 */
	public function getItem() : Item
	{
		return clone $this->item;
	}

	/**
	 * Returns a BlockTransaction object containing all the block positions that will be changed by this event, and the
	 * states they will be changed to.
	 *
	 * This will usually contain only one block, but may contain more if the block being placed is a multi-block
	 * structure such as a door or bed.
	 */
	public function getTransaction() : BlockTransaction
	{
		return $this->transaction;
	}

	public function getBlockAgainst() : Block
	{
		return $this->blockAgainst;
	}
}
