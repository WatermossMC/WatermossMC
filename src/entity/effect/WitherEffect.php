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
use watermossmc\event\entity\EntityDamageEvent;

class WitherEffect extends Effect
{
	public function canTick(EffectInstance $instance) : bool
	{
		if (($interval = (40 >> $instance->getAmplifier())) > 0) {
			return ($instance->getDuration() % $interval) === 0;
		}
		return true;
	}

	public function applyEffect(Living $entity, EffectInstance $instance, float $potency = 1.0, ?Entity $source = null) : void
	{
		$ev = new EntityDamageEvent($entity, EntityDamageEvent::CAUSE_MAGIC, 1);
		$entity->attack($ev);
	}
}
