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

namespace watermossmc\entity\projectile;

use watermossmc\block\Block;
use watermossmc\block\BlockTypeIds;
use watermossmc\block\VanillaBlocks;
use watermossmc\event\entity\ProjectileHitEvent;
use watermossmc\item\VanillaItems;
use watermossmc\math\AxisAlignedBB;
use watermossmc\math\RayTraceResult;
use watermossmc\math\Vector3;
use watermossmc\network\mcpe\protocol\types\entity\EntityIds;
use watermossmc\world\particle\ItemBreakParticle;
use watermossmc\world\sound\IceBombHitSound;

class IceBomb extends Throwable
{
	public static function getNetworkTypeId() : string
	{
		return EntityIds::ICE_BOMB;
	}

	public function getResultDamage() : int
	{
		return -1;
	}

	protected function calculateInterceptWithBlock(Block $block, Vector3 $start, Vector3 $end) : ?RayTraceResult
	{
		if ($block->getTypeId() === BlockTypeIds::WATER) {
			$pos = $block->getPosition();

			return AxisAlignedBB::one()->offset($pos->x, $pos->y, $pos->z)->calculateIntercept($start, $end);
		}

		return parent::calculateInterceptWithBlock($block, $start, $end);
	}

	protected function onHit(ProjectileHitEvent $event) : void
	{
		$world = $this->getWorld();
		$pos = $this->location;

		$world->addSound($pos, new IceBombHitSound());
		$itemBreakParticle = new ItemBreakParticle(VanillaItems::ICE_BOMB());
		for ($i = 0; $i < 6; ++$i) {
			$world->addParticle($pos, $itemBreakParticle);
		}
	}

	protected function onHitBlock(Block $blockHit, RayTraceResult $hitResult) : void
	{
		parent::onHitBlock($blockHit, $hitResult);

		$pos = $blockHit->getPosition();
		$world = $pos->getWorld();
		$posX = $pos->getFloorX();
		$posY = $pos->getFloorY();
		$posZ = $pos->getFloorZ();

		$ice = VanillaBlocks::ICE();
		for ($x = $posX - 1; $x <= $posX + 1; $x++) {
			for ($y = $posY - 1; $y <= $posY + 1; $y++) {
				for ($z = $posZ - 1; $z <= $posZ + 1; $z++) {
					if ($world->getBlockAt($x, $y, $z)->getTypeId() === BlockTypeIds::WATER) {
						$world->setBlockAt($x, $y, $z, $ice);
					}
				}
			}
		}
	}
}
