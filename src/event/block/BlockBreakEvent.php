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
use watermossmc\item\Item;
use watermossmc\player\Player;

/**
 * Called when a player destroys a block somewhere in the world.
 */
class BlockBreakEvent extends BlockEvent implements Cancellable
{
	use CancellableTrait;

	/** @var Item[] */
	protected array $blockDrops = [];

	/**
	 * @param Item[] $drops
	 */
	public function __construct(
		protected Player $player,
		Block $block,
		protected Item $item,
		protected bool $instaBreak = false,
		array $drops = [],
		protected int $xpDrops = 0
	) {
		parent::__construct($block);
		$this->setDrops($drops);
	}

	/**
	 * Returns the player who is destroying the block.
	 */
	public function getPlayer() : Player
	{
		return $this->player;
	}

	/**
	 * Returns the item used to destroy the block.
	 */
	public function getItem() : Item
	{
		return clone $this->item;
	}

	/**
	 * Returns whether the block may be broken in less than the amount of time calculated. This is usually true for
	 * creative players.
	 */
	public function getInstaBreak() : bool
	{
		return $this->instaBreak;
	}

	public function setInstaBreak(bool $instaBreak) : void
	{
		$this->instaBreak = $instaBreak;
	}

	/**
	 * @return Item[]
	 */
	public function getDrops() : array
	{
		return $this->blockDrops;
	}

	/**
	 * @param Item[] $drops
	 */
	public function setDrops(array $drops) : void
	{
		$this->setDropsVariadic(...$drops);
	}

	/**
	 * Variadic hack for easy array member type enforcement.
	 */
	public function setDropsVariadic(Item ...$drops) : void
	{
		$this->blockDrops = $drops;
	}

	/**
	 * Returns how much XP will be dropped by breaking this block.
	 */
	public function getXpDropAmount() : int
	{
		return $this->xpDrops;
	}

	/**
	 * Sets how much XP will be dropped by breaking this block.
	 */
	public function setXpDropAmount(int $amount) : void
	{
		if ($amount < 0) {
			throw new \InvalidArgumentException("Amount must be at least zero");
		}
		$this->xpDrops = $amount;
	}
}
