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

use watermossmc\color\Color;
use watermossmc\entity\Entity;
use watermossmc\entity\Living;
use watermossmc\event\entity\EntityDamageEvent;
use watermossmc\lang\Translatable;

class PoisonEffect extends Effect
{
	private bool $fatal;

	public function __construct(Translatable|string $name, Color $color, bool $isBad = false, int $defaultDuration = 600, bool $hasBubbles = true, bool $fatal = false)
	{
		parent::__construct($name, $color, $isBad, $defaultDuration, $hasBubbles);
		$this->fatal = $fatal;
	}

	public function canTick(EffectInstance $instance) : bool
	{
		if (($interval = (25 >> $instance->getAmplifier())) > 0) {
			return ($instance->getDuration() % $interval) === 0;
		}
		return true;
	}

	public function applyEffect(Living $entity, EffectInstance $instance, float $potency = 1.0, ?Entity $source = null) : void
	{
		if ($entity->getHealth() > 1 || $this->fatal) {
			$ev = new EntityDamageEvent($entity, EntityDamageEvent::CAUSE_MAGIC, 1);
			$entity->attack($ev);
		}
	}
}
