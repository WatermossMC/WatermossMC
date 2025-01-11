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

namespace watermossmc\item;

use watermossmc\entity\Location;
use watermossmc\entity\projectile\Throwable;
use watermossmc\event\entity\ProjectileLaunchEvent;
use watermossmc\math\Vector3;
use watermossmc\player\Player;
use watermossmc\world\sound\ThrowSound;

abstract class ProjectileItem extends Item
{
	abstract public function getThrowForce() : float;

	abstract protected function createEntity(Location $location, Player $thrower) : Throwable;

	public function onClickAir(Player $player, Vector3 $directionVector, array &$returnedItems) : ItemUseResult
	{
		$location = $player->getLocation();

		$projectile = $this->createEntity(Location::fromObject($player->getEyePos(), $player->getWorld(), $location->yaw, $location->pitch), $player);
		$projectile->setMotion($directionVector->multiply($this->getThrowForce()));

		$projectileEv = new ProjectileLaunchEvent($projectile);
		$projectileEv->call();
		if ($projectileEv->isCancelled()) {
			$projectile->flagForDespawn();
			return ItemUseResult::FAIL;
		}

		$projectile->spawnToAll();

		$location->getWorld()->addSound($location, new ThrowSound());

		$this->pop();

		return ItemUseResult::SUCCESS;
	}
}
