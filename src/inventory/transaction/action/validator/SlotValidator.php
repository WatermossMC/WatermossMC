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

namespace watermossmc\inventory\transaction\action\validator;

use watermossmc\inventory\Inventory;
use watermossmc\inventory\transaction\TransactionValidationException;
use watermossmc\item\Item;

/**
 * Validates a slot placement in an inventory.
 */
interface SlotValidator
{
	/**
	 * Returns null if the slot placement is valid, or a TransactionValidationException if it is not.
	 */
	public function validate(Inventory $inventory, Item $item, int $slot) : ?TransactionValidationException;
}
