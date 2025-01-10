<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.watermossmc.net/
 *
 *
 */

declare(strict_types=1);

/*
 * This file is part of the WatermossMC
 * (c) 2025 WatermossMC <gameplaytebakgambard>
 *
 * @License Apache 2.0
 */

namespace watermossmc\event\inventory;

use watermossmc\event\Cancellable;
use watermossmc\event\CancellableTrait;
use watermossmc\event\Event;
use watermossmc\inventory\transaction\InventoryTransaction;

/**
 * Called when a player performs actions involving items in inventories.
 *
 * This may involve multiple inventories, and may include actions such as:
 * - moving items from one slot to another
 * - splitting itemstacks
 * - dragging itemstacks across inventory slots (slot painting)
 * - dropping an item on the ground
 * - taking an item from the creative inventory menu
 * - destroying (trashing) an item
 *
 * @see https://doc.pmmp.io/en/rtfd/developer-reference/inventory-transactions.html for more information on inventory transactions
 */
class InventoryTransactionEvent extends Event implements Cancellable
{
	use CancellableTrait;

	public function __construct(private InventoryTransaction $transaction)
	{
	}

	public function getTransaction() : InventoryTransaction
	{
		return $this->transaction;
	}
}
