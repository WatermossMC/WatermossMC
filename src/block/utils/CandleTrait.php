<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.watermossmc.net/
 *
 *
 */

declare(strict_types=1);

/*
 * This file is part of the WatermossMC
 * (c) 2025 WatermossMC <gameplaytebakgambard>
 *
 * @License Apache 2.0
 */

namespace watermossmc\block\utils;

use watermossmc\block\Block;
use watermossmc\entity\projectile\Projectile;
use watermossmc\item\Durable;
use watermossmc\item\enchantment\VanillaEnchantments;
use watermossmc\item\Item;
use watermossmc\item\ItemTypeIds;
use watermossmc\math\RayTraceResult;
use watermossmc\math\Vector3;
use watermossmc\player\Player;
use watermossmc\world\sound\BlazeShootSound;
use watermossmc\world\sound\FireExtinguishSound;
use watermossmc\world\sound\FlintSteelSound;

trait CandleTrait
{
	use LightableTrait;

	public function getLightLevel() : int
	{
		return $this->lit ? 3 : 0;
	}

	/** @see Block::onInteract() */
	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null, array &$returnedItems = []) : bool
	{
		if ($item->getTypeId() === ItemTypeIds::FIRE_CHARGE || $item->getTypeId() === ItemTypeIds::FLINT_AND_STEEL || $item->hasEnchantment(VanillaEnchantments::FIRE_ASPECT())) {
			if ($this->lit) {
				return true;
			}
			if ($item instanceof Durable) {
				$item->applyDamage(1);
			} elseif ($item->getTypeId() === ItemTypeIds::FIRE_CHARGE) {
				$item->pop();
				//TODO: not sure if this is intentional, but it's what Bedrock currently does as of 1.20.10
				$this->position->getWorld()->addSound($this->position, new BlazeShootSound());
			}
			$this->position->getWorld()->addSound($this->position, new FlintSteelSound());
			$this->position->getWorld()->setBlock($this->position, $this->setLit(true));

			return true;
		}
		if ($item->isNull()) { //candle can only be extinguished with an empty hand
			if (!$this->lit) {
				return true;
			}
			$this->position->getWorld()->addSound($this->position, new FireExtinguishSound());
			$this->position->getWorld()->setBlock($this->position, $this->setLit(false));

			return true;
		}

		//yes, this is intentional! in vanilla, if the candle is not interacted with, a block is placed.
		return false;
	}

	/** @see Block::onProjectileHit() */
	public function onProjectileHit(Projectile $projectile, RayTraceResult $hitResult) : void
	{
		if (!$this->lit && $projectile->isOnFire()) {
			$this->position->getWorld()->setBlock($this->position, $this->setLit(true));
		}
	}
}
