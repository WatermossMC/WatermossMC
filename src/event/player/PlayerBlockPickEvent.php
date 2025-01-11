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
use watermossmc\player\Player;

/**
 * Called when a player middle-clicks on a block to get an item in creative mode.
 */
class PlayerBlockPickEvent extends PlayerEvent implements Cancellable
{
	use CancellableTrait;

	public function __construct(
		Player $player,
		private Block $blockClicked,
		private Item $resultItem
	) {
		$this->player = $player;
	}

	public function getBlock() : Block
	{
		return $this->blockClicked;
	}

	public function getResultItem() : Item
	{
		return $this->resultItem;
	}
}
