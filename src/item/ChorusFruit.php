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

use watermossmc\block\Liquid;
use watermossmc\entity\Living;
use watermossmc\math\Vector3;
use watermossmc\world\sound\EndermanTeleportSound;

use function min;
use function mt_rand;

class ChorusFruit extends Food
{
	public function getFoodRestore() : int
	{
		return 4;
	}

	public function getSaturationRestore() : float
	{
		return 2.4;
	}

	public function requiresHunger() : bool
	{
		return false;
	}

	public function onConsume(Living $consumer) : void
	{
		$world = $consumer->getWorld();

		$origin = $consumer->getPosition();
		$minX = $origin->getFloorX() - 8;
		$minY = min($origin->getFloorY(), $consumer->getWorld()->getMaxY()) - 8;
		$minZ = $origin->getFloorZ() - 8;

		$maxX = $minX + 16;
		$maxY = $minY + 16;
		$maxZ = $minZ + 16;

		$worldMinY = $world->getMinY();

		for ($attempts = 0; $attempts < 16; ++$attempts) {
			$x = mt_rand($minX, $maxX);
			$y = mt_rand($minY, $maxY);
			$z = mt_rand($minZ, $maxZ);

			while ($y >= $worldMinY && !$world->getBlockAt($x, $y, $z)->isSolid()) {
				$y--;
			}
			if ($y < $worldMinY) {
				continue;
			}

			$blockUp = $world->getBlockAt($x, $y + 1, $z);
			$blockUp2 = $world->getBlockAt($x, $y + 2, $z);
			if ($blockUp->isSolid() || $blockUp instanceof Liquid || $blockUp2->isSolid() || $blockUp2 instanceof Liquid) {
				continue;
			}

			//Sounds are broadcasted at both source and destination
			$world->addSound($origin, new EndermanTeleportSound());
			$consumer->teleport($target = new Vector3($x + 0.5, $y + 1, $z + 0.5));
			$world->addSound($target, new EndermanTeleportSound());

			break;
		}
	}

	public function getCooldownTicks() : int
	{
		return 20;
	}

	public function getCooldownTag() : ?string
	{
		return ItemCooldownTags::CHORUS_FRUIT;
	}
}
