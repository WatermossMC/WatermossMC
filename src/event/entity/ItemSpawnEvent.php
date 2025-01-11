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

/**
 * Called when an item is spawned or loaded.
 *
 * Some possible reasons include:
 * - item is loaded from disk
 * - player dropping an item
 * - block drops
 * - loot of a player or entity
 *
 * @see PlayerDropItemEvent
 * @phpstan-extends EntityEvent<ItemEntity>
 */
class ItemSpawnEvent extends EntityEvent
{
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
