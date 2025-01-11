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

namespace watermossmc\event\entity;

use watermossmc\entity\effect\EffectInstance;
use watermossmc\entity\Entity;

/**
 * Called when an effect is added to an Entity.
 */
class EntityEffectAddEvent extends EntityEffectEvent
{
	public function __construct(Entity $entity, EffectInstance $effect, private ?EffectInstance $oldEffect = null)
	{
		parent::__construct($entity, $effect);
	}

	/**
	 * Returns whether the effect addition will replace an existing effect already applied to the entity.
	 */
	public function willModify() : bool
	{
		return $this->hasOldEffect();
	}

	public function hasOldEffect() : bool
	{
		return $this->oldEffect instanceof EffectInstance;
	}

	public function getOldEffect() : ?EffectInstance
	{
		return $this->oldEffect;
	}
}
