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

use watermossmc\entity\Entity;
use watermossmc\event\Cancellable;
use watermossmc\event\CancellableTrait;
use watermossmc\math\Vector3;
use watermossmc\player\Player;

/**
 * Called when a player interacts with an entity (e.g. shearing a sheep, naming a mob using a nametag).
 */
class PlayerEntityInteractEvent extends PlayerEvent implements Cancellable
{
	use CancellableTrait;

	public function __construct(
		Player $player,
		private Entity $entity,
		private Vector3 $clickPos
	) {
		$this->player = $player;
	}

	public function getEntity() : Entity
	{
		return $this->entity;
	}

	/**
	 * Returns the absolute coordinates of the click. This is usually on the surface of the entity's hitbox.
	 */
	public function getClickPosition() : Vector3
	{
		return $this->clickPos;
	}
}
