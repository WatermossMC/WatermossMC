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

use watermossmc\block\utils\DripleafState;
use watermossmc\data\runtime\RuntimeDataDescriber;
use watermossmc\entity\Entity;
use watermossmc\entity\projectile\Projectile;
use watermossmc\math\AxisAlignedBB;
use watermossmc\math\Facing;
use watermossmc\math\RayTraceResult;
use watermossmc\world\sound\DripleafTiltDownSound;
use watermossmc\world\sound\DripleafTiltUpSound;

class BigDripleafHead extends BaseBigDripleaf
{
	protected DripleafState $leafState = DripleafState::STABLE;

	protected function describeBlockOnlyState(RuntimeDataDescriber $w) : void
	{
		parent::describeBlockOnlyState($w);
		$w->enum($this->leafState);
	}

	protected function isHead() : bool
	{
		return true;
	}

	public function getLeafState() : DripleafState
	{
		return $this->leafState;
	}

	/** @return $this */
	public function setLeafState(DripleafState $leafState) : self
	{
		$this->leafState = $leafState;
		return $this;
	}

	public function hasEntityCollision() : bool
	{
		return true;
	}

	private function setTiltAndScheduleTick(DripleafState $tilt) : void
	{
		$this->position->getWorld()->setBlock($this->position, $this->setLeafState($tilt));
		$delay = $tilt->getScheduledUpdateDelayTicks();
		if ($delay !== null) {
			$this->position->getWorld()->scheduleDelayedBlockUpdate($this->position, $delay);
		}
	}

	private function getLeafTopOffset() : float
	{
		return match($this->leafState) {
			DripleafState::STABLE, DripleafState::UNSTABLE => 1 / 16,
			DripleafState::PARTIAL_TILT => 3 / 16,
			default => 0
		};
	}

	public function onEntityInside(Entity $entity) : bool
	{
		if (!$entity instanceof Projectile && $this->leafState === DripleafState::STABLE) {
			//the entity must be standing on top of the leaf - do not collapse if the entity is standing underneath
			$intersection = AxisAlignedBB::one()
				->offset($this->position->x, $this->position->y, $this->position->z)
				->trim(Facing::DOWN, 1 - $this->getLeafTopOffset());
			if ($entity->getBoundingBox()->intersectsWith($intersection)) {
				$this->setTiltAndScheduleTick(DripleafState::UNSTABLE);
				return false;
			}
		}
		return true;
	}

	public function onProjectileHit(Projectile $projectile, RayTraceResult $hitResult) : void
	{
		if ($this->leafState !== DripleafState::FULL_TILT) {
			$this->setTiltAndScheduleTick(DripleafState::FULL_TILT);
			$this->position->getWorld()->addSound($this->position, new DripleafTiltDownSound());
		}
	}

	public function onScheduledUpdate() : void
	{
		if ($this->leafState !== DripleafState::STABLE) {
			if ($this->leafState === DripleafState::FULL_TILT) {
				$this->position->getWorld()->setBlock($this->position, $this->setLeafState(DripleafState::STABLE));
				$this->position->getWorld()->addSound($this->position, new DripleafTiltUpSound());
			} else {
				$this->setTiltAndScheduleTick(match($this->leafState) {
					DripleafState::UNSTABLE => DripleafState::PARTIAL_TILT,
					DripleafState::PARTIAL_TILT => DripleafState::FULL_TILT,
				});
				$this->position->getWorld()->addSound($this->position, new DripleafTiltDownSound());
			}
		}
	}

	protected function recalculateCollisionBoxes() : array
	{
		if ($this->leafState !== DripleafState::FULL_TILT) {
			return [
				AxisAlignedBB::one()
					->trim(Facing::DOWN, 11 / 16)
					->trim(Facing::UP, $this->getLeafTopOffset())
			];
		}
		return [];
	}
}
