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
use watermossmc\lang\Translatable;
use watermossmc\utils\NotCloneable;
use watermossmc\utils\NotSerializable;

class Effect
{
	use NotCloneable;
	use NotSerializable;

	/**
	 * @param Translatable|string $name       Translation key used for effect name
	 * @param Color               $color      Color of bubbles given by this effect
	 * @param bool                $bad        Whether the effect is harmful
	 * @param bool                $hasBubbles Whether the effect has potion bubbles. Some do not (e.g. Instant Damage has its own particles instead of bubbles)
	 */
	public function __construct(
		protected Translatable|string $name,
		protected Color $color,
		protected bool $bad = false,
		private int $defaultDuration = 600,
		protected bool $hasBubbles = true
	) {
	}

	/**
	 * Returns the translation key used to translate this effect's name.
	 */
	public function getName() : Translatable|string
	{
		return $this->name;
	}

	/**
	 * Returns a Color object representing this effect's particle colour.
	 */
	public function getColor() : Color
	{
		return $this->color;
	}

	/**
	 * Returns whether this effect is harmful.
	 * TODO: implement inverse effect results for undead mobs
	 */
	public function isBad() : bool
	{
		return $this->bad;
	}

	/**
	 * Returns the default duration (in ticks) this effect will apply for if a duration is not specified.
	 */
	public function getDefaultDuration() : int
	{
		return $this->defaultDuration;
	}

	/**
	 * Returns whether this effect will give the subject potion bubbles.
	 */
	public function hasBubbles() : bool
	{
		return $this->hasBubbles;
	}

	/**
	 * Returns whether the effect will do something on the current tick.
	 */
	public function canTick(EffectInstance $instance) : bool
	{
		return false;
	}

	/**
	 * Applies effect results to an entity. This will not be called unless canTick() returns true.
	 */
	public function applyEffect(Living $entity, EffectInstance $instance, float $potency = 1.0, ?Entity $source = null) : void
	{

	}

	/**
	 * Applies effects to the entity when the effect is first added.
	 */
	public function add(Living $entity, EffectInstance $instance) : void
	{

	}

	/**
	 * Removes the effect from the entity, resetting any changed values back to their original defaults.
	 */
	public function remove(Living $entity, EffectInstance $instance) : void
	{

	}
}
