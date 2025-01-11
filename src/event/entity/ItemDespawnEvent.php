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

namespace watermossmc\event\entity;

use watermossmc\entity\object\ItemEntity;
use watermossmc\event\Cancellable;
use watermossmc\event\CancellableTrait;

/**
 * Called when a dropped item tries to despawn due to its despawn delay running out.
 * Cancelling the event will reset the despawn delay to default (5 minutes).
 *
 * @phpstan-extends EntityEvent<ItemEntity>
 */
class ItemDespawnEvent extends EntityEvent implements Cancellable
{
	use CancellableTrait;

	public function __construct(ItemEntity $item)
	{
		$this->entity = $item;
	}

	/**
	 * @return ItemEntity
	 */
	public function getEntity()
	{
		return $this->entity;
	}
}
