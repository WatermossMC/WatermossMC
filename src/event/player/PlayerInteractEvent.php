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

use watermossmc\block\Block;
use watermossmc\event\Cancellable;
use watermossmc\event\CancellableTrait;
use watermossmc\item\Item;
use watermossmc\math\Vector3;
use watermossmc\player\Player;

/**
 * Called when a player interacts or touches a block.
 * This is called for both left click (start break) and right click (use).
 */
class PlayerInteractEvent extends PlayerEvent implements Cancellable
{
	use CancellableTrait;

	public const LEFT_CLICK_BLOCK = 0;
	public const RIGHT_CLICK_BLOCK = 1;

	protected Vector3 $touchVector;

	protected bool $useItem = true;
	protected bool $useBlock = true;

	public function __construct(
		Player $player,
		protected Item $item,
		protected Block $blockTouched,
		?Vector3 $touchVector,
		protected int $blockFace,
		protected int $action = PlayerInteractEvent::RIGHT_CLICK_BLOCK
	) {
		$this->player = $player;
		$this->touchVector = $touchVector ?? Vector3::zero();
	}

	public function getAction() : int
	{
		return $this->action;
	}

	public function getItem() : Item
	{
		return clone $this->item;
	}

	public function getBlock() : Block
	{
		return $this->blockTouched;
	}

	public function getTouchVector() : Vector3
	{
		return $this->touchVector;
	}

	public function getFace() : int
	{
		return $this->blockFace;
	}

	/**
	 * Returns whether the item may react to the interaction. If disabled, items such as spawn eggs will not activate.
	 * This does NOT prevent blocks from being placed - it makes the item behave as if the player is sneaking.
	 */
	public function useItem() : bool
	{
		return $this->useItem;
	}

	/**
	 * Sets whether the used item may react to the interaction. If false, items such as spawn eggs will not activate.
	 * This does NOT prevent blocks from being placed - it makes the item behave as if the player is sneaking.
	 */
	public function setUseItem(bool $useItem) : void
	{
		$this->useItem = $useItem;
	}

	/**
	 * Returns whether the block may react to the interaction. If false, doors, fence gates and trapdoors will not
	 * respond, containers will not open, etc.
	 */
	public function useBlock() : bool
	{
		return $this->useBlock;
	}

	/**
	 * Sets whether the block may react to the interaction. If false, doors, fence gates and trapdoors will not
	 * respond, containers will not open, etc.
	 */
	public function setUseBlock(bool $useBlock) : void
	{
		$this->useBlock = $useBlock;
	}
}
