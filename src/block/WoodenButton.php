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

use watermossmc\block\utils\WoodTypeTrait;

class WoodenButton extends Button
{
	use WoodTypeTrait;

	protected function getActivationTime() : int
	{
		return 30;
	}

	public function hasEntityCollision() : bool
	{
		return false; //TODO: arrows activate wooden buttons
	}

	public function getFuelTime() : int
	{
		return $this->woodType->isFlammable() ? 100 : 0;
	}
}
