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

use watermossmc\entity\Living;
use watermossmc\player\Player;

class MilkBucket extends Item implements ConsumableItem
{
	public function getMaxStackSize() : int
	{
		return 1;
	}

	public function getResidue() : Item
	{
		return VanillaItems::BUCKET();
	}

	public function getAdditionalEffects() : array
	{
		return [];
	}

	public function onConsume(Living $consumer) : void
	{
		$consumer->getEffects()->clear();
	}

	public function canStartUsingItem(Player $player) : bool
	{
		return true;
	}
}
