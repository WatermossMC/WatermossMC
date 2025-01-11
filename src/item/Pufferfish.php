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

use watermossmc\entity\effect\EffectInstance;
use watermossmc\entity\effect\VanillaEffects;

class Pufferfish extends Food
{
	public function getFoodRestore() : int
	{
		return 1;
	}

	public function getSaturationRestore() : float
	{
		return 0.2;
	}

	public function getAdditionalEffects() : array
	{
		return [
			new EffectInstance(VanillaEffects::HUNGER(), 300, 2),
			new EffectInstance(VanillaEffects::POISON(), 1200, 3),
			new EffectInstance(VanillaEffects::NAUSEA(), 300, 1)
		];
	}
}
