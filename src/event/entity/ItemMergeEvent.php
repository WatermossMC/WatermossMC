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
 * Called when an item entity tries to merge into another item entity.
 *
 * @phpstan-extends EntityEvent<ItemEntity>
 */
class ItemMergeEvent extends EntityEvent implements Cancellable
{
	use CancellableTrait;

	public function __construct(
		ItemEntity $entity,
		protected ItemEntity $target
	) {
		$this->entity = $entity;
	}

	/**
	 * Returns the merge destination.
	 */
	public function getTarget() : ItemEntity
	{
		return $this->target;
	}

}
