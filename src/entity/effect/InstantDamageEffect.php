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
use watermossmc\event\entity\EntityDamageByChildEntityEvent;
use watermossmc\event\entity\EntityDamageByEntityEvent;
use watermossmc\event\entity\EntityDamageEvent;

class InstantDamageEffect extends InstantEffect
{
	public function applyEffect(Living $entity, EffectInstance $instance, float $potency = 1.0, ?Entity $source = null) : void
	{
		//TODO: add particles (witch spell)
		$damage = (6 << $instance->getAmplifier()) * $potency;
		if ($source !== null) {
			$sourceOwner = $source->getOwningEntity();
			if ($sourceOwner !== null) {
				$ev = new EntityDamageByChildEntityEvent($sourceOwner, $source, $entity, EntityDamageEvent::CAUSE_MAGIC, $damage);
			} else {
				$ev = new EntityDamageByEntityEvent($source, $entity, EntityDamageEvent::CAUSE_MAGIC, $damage);
			}
		} else {
			$ev = new EntityDamageEvent($entity, EntityDamageEvent::CAUSE_MAGIC, $damage);
		}
		$entity->attack($ev);
	}
}
