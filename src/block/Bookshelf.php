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

namespace watermossmc\block;

use watermossmc\item\Item;
use watermossmc\item\VanillaItems;

class Bookshelf extends Opaque
{
	public function getDropsForCompatibleTool(Item $item) : array
	{
		return [
			VanillaItems::BOOK()->setCount(3)
		];
	}

	public function isAffectedBySilkTouch() : bool
	{
		return true;
	}

	public function getFuelTime() : int
	{
		return 300;
	}

	public function getFlameEncouragement() : int
	{
		return 30;
	}

	public function getFlammability() : int
	{
		return 20;
	}
}
