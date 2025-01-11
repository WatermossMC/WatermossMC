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

namespace watermossmc\entity;

use watermossmc\event\entity\EntityDamageEvent;
use watermossmc\network\mcpe\protocol\types\entity\EntityMetadataCollection;
use watermossmc\network\mcpe\protocol\types\entity\EntityMetadataFlags;

abstract class WaterAnimal extends Living implements Ageable
{
	protected bool $baby = false;

	public function isBaby() : bool
	{
		return $this->baby;
	}

	public function canBreathe() : bool
	{
		return $this->isUnderwater();
	}

	public function onAirExpired() : void
	{
		$ev = new EntityDamageEvent($this, EntityDamageEvent::CAUSE_SUFFOCATION, 2);
		$this->attack($ev);
	}

	protected function syncNetworkData(EntityMetadataCollection $properties) : void
	{
		parent::syncNetworkData($properties);
		$properties->setGenericFlag(EntityMetadataFlags::BABY, $this->baby);
	}
}
