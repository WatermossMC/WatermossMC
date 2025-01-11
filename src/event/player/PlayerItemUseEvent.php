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
use watermossmc\math\Vector3;
use watermossmc\player\Player;

/**
 * Called when a player uses its held item, for example when throwing a projectile.
 */
class PlayerItemUseEvent extends PlayerEvent implements Cancellable
{
	use CancellableTrait;

	public function __construct(
		Player $player,
		private Item $item,
		private Vector3 $directionVector
	) {
		$this->player = $player;
	}

	/**
	 * Returns the item used.
	 */
	public function getItem() : Item
	{
		return clone $this->item;
	}

	/**
	 * Returns the direction the player is aiming when activating this item. Used for projectile direction.
	 */
	public function getDirectionVector() : Vector3
	{
		return $this->directionVector;
	}
}
