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
use watermossmc\utils\Utils;

class CallbackSlotValidator implements SlotValidator
{
	/**
	 * @phpstan-param \Closure(Inventory, Item, int) : ?TransactionValidationException $validate
	 */
	public function __construct(
		private \Closure $validate
	) {
		Utils::validateCallableSignature(function (Inventory $inventory, Item $item, int $slot) : ?TransactionValidationException { return null; }, $validate);
	}

	public function validate(Inventory $inventory, Item $item, int $slot) : ?TransactionValidationException
	{
		return ($this->validate)($inventory, $item, $slot);
	}
}
