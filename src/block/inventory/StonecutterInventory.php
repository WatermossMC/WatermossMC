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

namespace watermossmc\block\inventory;

use watermossmc\inventory\SimpleInventory;
use watermossmc\inventory\TemporaryInventory;
use watermossmc\world\Position;

class StonecutterInventory extends SimpleInventory implements BlockInventory, TemporaryInventory
{
	use BlockInventoryTrait;

	public const SLOT_INPUT = 0;

	public function __construct(Position $holder)
	{
		$this->holder = $holder;
		parent::__construct(1);
	}
}
