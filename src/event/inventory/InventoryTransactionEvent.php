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
