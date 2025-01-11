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

namespace watermossmc\block;

use watermossmc\block\utils\AgeableTrait;
use watermossmc\block\utils\BlockEventHelper;
use watermossmc\block\utils\StaticSupportTrait;
use watermossmc\block\utils\SupportType;
use watermossmc\entity\Entity;
use watermossmc\event\entity\EntityDamageByBlockEvent;
use watermossmc\event\entity\EntityDamageEvent;
use watermossmc\math\AxisAlignedBB;
use watermossmc\math\Facing;

class Cactus extends Transparent
{
	use AgeableTrait;
	use StaticSupportTrait;

	public const MAX_AGE = 15;

	public function hasEntityCollision() : bool
	{
		return true;
	}

	/**
	 * @return AxisAlignedBB[]
	 */
	protected function recalculateCollisionBoxes() : array
	{
		$shrinkSize = 1 / 16;
		return [AxisAlignedBB::one()->contract($shrinkSize, 0, $shrinkSize)->trim(Facing::UP, $shrinkSize)];
	}

	public function getSupportType(int $facing) : SupportType
	{
		return SupportType::NONE;
	}

	public function onEntityInside(Entity $entity) : bool
	{
		$ev = new EntityDamageByBlockEvent($this, $entity, EntityDamageEvent::CAUSE_CONTACT, 1);
		$entity->attack($ev);
		return true;
	}

	private function canBeSupportedAt(Block $block) : bool
	{
		$supportBlock = $block->getSide(Facing::DOWN);
		if (!$supportBlock->hasSameTypeId($this) && !$supportBlock->hasTypeTag(BlockTypeTags::SAND)) {
			return false;
		}
		foreach (Facing::HORIZONTAL as $side) {
			if ($block->getSide($side)->isSolid()) {
				return false;
			}
		}

		return true;
	}

	public function ticksRandomly() : bool
	{
		return true;
	}

	public function onRandomTick() : void
	{
		if (!$this->getSide(Facing::DOWN)->hasSameTypeId($this)) {
			$world = $this->position->getWorld();
			if ($this->age === self::MAX_AGE) {
				for ($y = 1; $y < 3; ++$y) {
					if (!$world->isInWorld($this->position->x, $this->position->y + $y, $this->position->z)) {
						break;
					}
					$b = $world->getBlockAt($this->position->x, $this->position->y + $y, $this->position->z);
					if ($b->getTypeId() === BlockTypeIds::AIR) {
						BlockEventHelper::grow($b, VanillaBlocks::CACTUS(), null);
					} else {
						break;
					}
				}
				$this->age = 0;
				$world->setBlock($this->position, $this, update: false);
			} else {
				++$this->age;
				$world->setBlock($this->position, $this, update: false);
			}
		}
	}
}
