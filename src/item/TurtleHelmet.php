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
use watermossmc\entity\Human;
use watermossmc\entity\Living;

class TurtleHelmet extends Armor
{
	public function onTickWorn(Living $entity) : bool
	{
		if ($entity instanceof Human && !$entity->isUnderwater()) {
			$entity->getEffects()->add(new EffectInstance(VanillaEffects::WATER_BREATHING(), 200, 0, false));
			return true;
		}

		return false;
	}
}
