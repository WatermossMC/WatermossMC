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

namespace watermossmc\inventory;

use watermossmc\inventory\transaction\action\validator\SlotValidator;
use watermossmc\utils\ObjectSet;

/**
 * A "slot validated inventory" has validators which may restrict items
 * from being placed in particular slots of the inventory when transactions are executed.
 *
 * @phpstan-type SlotValidators ObjectSet<SlotValidator>
 */
interface SlotValidatedInventory
{
	/**
	 * Returns a set of validators that will be used to determine whether an item can be placed in a particular slot.
	 * All validators need to return null for the transaction to be allowed.
	 * If one of the validators returns an exception, the transaction will be cancelled.
	 *
	 * There is no guarantee that the validators will be called in any particular order.
	 *
	 * @phpstan-return SlotValidators
	 */
	public function getSlotValidators() : ObjectSet;
}
