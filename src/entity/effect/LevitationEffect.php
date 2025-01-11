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

namespace watermossmc\entity\effect;

use watermossmc\entity\Entity;
use watermossmc\entity\Living;
use watermossmc\player\Player;

class LevitationEffect extends Effect
{
	public function canTick(EffectInstance $instance) : bool
	{
		return true;
	}

	public function applyEffect(Living $entity, EffectInstance $instance, float $potency = 1.0, ?Entity $source = null) : void
	{
		if (!($entity instanceof Player)) { //TODO: ugly hack, player motion isn't updated properly by the server yet :(
			$entity->addMotion(0, ($instance->getEffectLevel() / 20 - $entity->getMotion()->y) / 5, 0);
		}
	}

	public function add(Living $entity, EffectInstance $instance) : void
	{
		$entity->setHasGravity(false);
	}

	public function remove(Living $entity, EffectInstance $instance) : void
	{
		$entity->setHasGravity();
	}
}
