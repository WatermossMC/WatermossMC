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
use watermossmc\world\Position;

class BrewingStandInventory extends SimpleInventory implements BlockInventory
{
	use BlockInventoryTrait;

	public const SLOT_INGREDIENT = 0;
	public const SLOT_BOTTLE_LEFT = 1;
	public const SLOT_BOTTLE_MIDDLE = 2;
	public const SLOT_BOTTLE_RIGHT = 3;
	public const SLOT_FUEL = 4;

	public function __construct(Position $holder, int $size = 5)
	{
		$this->holder = $holder;
		parent::__construct($size);
	}
}
