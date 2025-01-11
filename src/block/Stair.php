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

use watermossmc\block\utils\HorizontalFacingTrait;
use watermossmc\block\utils\StairShape;
use watermossmc\block\utils\SupportType;
use watermossmc\data\runtime\RuntimeDataDescriber;
use watermossmc\item\Item;
use watermossmc\math\Axis;
use watermossmc\math\AxisAlignedBB;
use watermossmc\math\Facing;
use watermossmc\math\Vector3;
use watermossmc\player\Player;
use watermossmc\world\BlockTransaction;

class Stair extends Transparent
{
	use HorizontalFacingTrait;

	protected bool $upsideDown = false;
	protected StairShape $shape = StairShape::STRAIGHT;

	protected function describeBlockOnlyState(RuntimeDataDescriber $w) : void
	{
		$w->horizontalFacing($this->facing);
		$w->bool($this->upsideDown);
	}

	public function readStateFromWorld() : Block
	{
		parent::readStateFromWorld();

		$this->collisionBoxes = null;

		$clockwise = Facing::rotateY($this->facing, true);
		if (($backFacing = $this->getPossibleCornerFacing(false)) !== null) {
			$this->shape = $backFacing === $clockwise ? StairShape::OUTER_RIGHT : StairShape::OUTER_LEFT;
		} elseif (($frontFacing = $this->getPossibleCornerFacing(true)) !== null) {
			$this->shape = $frontFacing === $clockwise ? StairShape::INNER_RIGHT : StairShape::INNER_LEFT;
		} else {
			$this->shape = StairShape::STRAIGHT;
		}

		return $this;
	}

	public function isUpsideDown() : bool
	{
		return $this->upsideDown;
	}

	/** @return $this */
	public function setUpsideDown(bool $upsideDown) : self
	{
		$this->upsideDown = $upsideDown;
		return $this;
	}

	public function getShape() : StairShape
	{
		return $this->shape;
	}

	/** @return $this */
	public function setShape(StairShape $shape) : self
	{
		$this->shape = $shape;
		return $this;
	}

	protected function recalculateCollisionBoxes() : array
	{
		$topStepFace = $this->upsideDown ? Facing::DOWN : Facing::UP;
		$bbs = [
			AxisAlignedBB::one()->trim($topStepFace, 0.5)
		];

		$topStep = AxisAlignedBB::one()
			->trim(Facing::opposite($topStepFace), 0.5)
			->trim(Facing::opposite($this->facing), 0.5);

		if ($this->shape === StairShape::OUTER_LEFT || $this->shape === StairShape::OUTER_RIGHT) {
			$topStep->trim(Facing::rotateY($this->facing, $this->shape === StairShape::OUTER_LEFT), 0.5);
		} elseif ($this->shape === StairShape::INNER_LEFT || $this->shape === StairShape::INNER_RIGHT) {
			//add an extra cube
			$bbs[] = AxisAlignedBB::one()
				->trim(Facing::opposite($topStepFace), 0.5)
				->trim($this->facing, 0.5) //avoid overlapping with main step
				->trim(Facing::rotateY($this->facing, $this->shape === StairShape::INNER_LEFT), 0.5);
		}

		$bbs[] = $topStep;

		return $bbs;
	}

	public function getSupportType(int $facing) : SupportType
	{
		if (
			$facing === Facing::UP && $this->upsideDown ||
			$facing === Facing::DOWN && !$this->upsideDown ||
			($facing === $this->facing && $this->shape !== StairShape::OUTER_LEFT && $this->shape !== StairShape::OUTER_RIGHT) ||
			($facing === Facing::rotate($this->facing, Axis::Y, false) && $this->shape === StairShape::INNER_LEFT) ||
			($facing === Facing::rotate($this->facing, Axis::Y, true) && $this->shape === StairShape::INNER_RIGHT)
		) {
			return SupportType::FULL;
		}
		return SupportType::NONE;
	}

	private function getPossibleCornerFacing(bool $oppositeFacing) : ?int
	{
		$side = $this->getSide($oppositeFacing ? Facing::opposite($this->facing) : $this->facing);
		return (
			$side instanceof Stair &&
			$side->upsideDown === $this->upsideDown &&
			Facing::axis($side->facing) !== Facing::axis($this->facing) //perpendicular
		) ? $side->facing : null;
	}

	public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool
	{
		if ($player !== null) {
			$this->facing = $player->getHorizontalFacing();
		}
		$this->upsideDown = (($clickVector->y > 0.5 && $face !== Facing::UP) || $face === Facing::DOWN);

		return parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player);
	}
}
