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

namespace watermossmc\item;

use watermossmc\entity\effect\VanillaEffects;
use watermossmc\entity\Living;

final class HoneyBottle extends Food
{
	public function getMaxStackSize() : int
	{
		return 16;
	}

	public function requiresHunger() : bool
	{
		return false;
	}

	public function getFoodRestore() : int
	{
		return 6;
	}

	public function getSaturationRestore() : float
	{
		return 1.2;
	}

	public function getResidue() : Item
	{
		return VanillaItems::GLASS_BOTTLE();
	}

	public function onConsume(Living $consumer) : void
	{
		$consumer->getEffects()->remove(VanillaEffects::POISON());
	}
}
