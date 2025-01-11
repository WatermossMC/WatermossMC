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

use watermossmc\entity\Entity;

/**
 * Called when an entity takes damage from an entity sourced from another entity, for example being hit by a snowball thrown by a Player.
 */
class EntityDamageByChildEntityEvent extends EntityDamageByEntityEvent
{
	private int $childEntityEid;

	/**
	 * @param float[] $modifiers
	 */
	public function __construct(Entity $damager, Entity $childEntity, Entity $entity, int $cause, float $damage, array $modifiers = [])
	{
		$this->childEntityEid = $childEntity->getId();
		parent::__construct($damager, $entity, $cause, $damage, $modifiers);
	}

	/**
	 * Returns the entity which caused the damage, or null if the entity has been killed or closed.
	 */
	public function getChild() : ?Entity
	{
		return $this->getEntity()->getWorld()->getServer()->getWorldManager()->findEntity($this->childEntityEid);
	}
}
