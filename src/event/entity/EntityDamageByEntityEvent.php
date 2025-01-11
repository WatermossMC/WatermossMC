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

use watermossmc\entity\effect\VanillaEffects;
use watermossmc\entity\Entity;
use watermossmc\entity\Living;

/**
 * Called when an entity takes damage from another entity.
 */
class EntityDamageByEntityEvent extends EntityDamageEvent
{
	private int $damagerEntityId;

	/**
	 * @param float[] $modifiers
	 */
	public function __construct(
		Entity $damager,
		Entity $entity,
		int $cause,
		float $damage,
		array $modifiers = [],
		private float $knockBack = Living::DEFAULT_KNOCKBACK_FORCE,
		private float $verticalKnockBackLimit = Living::DEFAULT_KNOCKBACK_VERTICAL_LIMIT
	) {
		$this->damagerEntityId = $damager->getId();
		parent::__construct($entity, $cause, $damage, $modifiers);
		$this->addAttackerModifiers($damager);
	}

	protected function addAttackerModifiers(Entity $damager) : void
	{
		if ($damager instanceof Living) { //TODO: move this to entity classes
			$effects = $damager->getEffects();
			if (($strength = $effects->get(VanillaEffects::STRENGTH())) !== null) {
				$this->setModifier($this->getBaseDamage() * 0.3 * $strength->getEffectLevel(), self::MODIFIER_STRENGTH);
			}

			if (($weakness = $effects->get(VanillaEffects::WEAKNESS())) !== null && $this->getCause() === EntityDamageEvent::CAUSE_ENTITY_ATTACK) {
				$this->setModifier(-($this->getBaseDamage() * 0.2 * $weakness->getEffectLevel()), self::MODIFIER_WEAKNESS);
			}
		}
	}

	/**
	 * Returns the attacking entity, or null if the attacker has been killed or closed.
	 */
	public function getDamager() : ?Entity
	{
		return $this->getEntity()->getWorld()->getServer()->getWorldManager()->findEntity($this->damagerEntityId);
	}

	/**
	 * Returns the force with which the victim will be knocked back from the attacking entity.
	 *
	 * @see Living::DEFAULT_KNOCKBACK_FORCE
	 */
	public function getKnockBack() : float
	{
		return $this->knockBack;
	}

	/**
	 * Sets the force with which the victim will be knocked back from the attacking entity.
	 * Larger values will knock the victim back further.
	 * Negative values will pull the victim towards the attacker.
	 */
	public function setKnockBack(float $knockBack) : void
	{
		$this->knockBack = $knockBack;
	}

	/**
	 * Returns the maximum upwards velocity the victim may have after being knocked back.
	 * This ensures that the victim doesn't fly up into the sky when high levels of knockback are applied.
	 *
	 * @see Living::DEFAULT_KNOCKBACK_VERTICAL_LIMIT
	 */
	public function getVerticalKnockBackLimit() : float
	{
		return $this->verticalKnockBackLimit;
	}

	/**
	 * Sets the maximum upwards velocity the victim may have after being knocked back.
	 * Larger values will allow the victim to fly higher if the knockback force is also large.
	 */
	public function setVerticalKnockBackLimit(float $verticalKnockBackLimit) : void
	{
		$this->verticalKnockBackLimit = $verticalKnockBackLimit;
	}
}
