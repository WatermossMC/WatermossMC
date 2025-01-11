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

abstract class Food extends Item implements FoodSourceItem
{
	public function requiresHunger() : bool
	{
		return true;
	}

	public function getResidue() : Item
	{
		return VanillaItems::AIR();
	}

	public function getAdditionalEffects() : array
	{
		return [];
	}

	public function onConsume(Living $consumer) : void
	{

	}

	public function canStartUsingItem(Player $player) : bool
	{
		return !$this->requiresHunger() || $player->canEat();
	}
}
