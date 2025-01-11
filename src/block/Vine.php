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

use watermossmc\data\runtime\RuntimeDataDescriber;
use watermossmc\entity\Entity;
use watermossmc\item\Item;
use watermossmc\math\Axis;
use watermossmc\math\Facing;
use watermossmc\math\Vector3;
use watermossmc\player\Player;
use watermossmc\world\BlockTransaction;

use function array_intersect_key;
use function count;

class Vine extends Flowable
{
	/** @var int[] */
	protected array $faces = [];

	protected function describeBlockOnlyState(RuntimeDataDescriber $w) : void
	{
		$w->horizontalFacingFlags($this->faces);
	}

	/** @return int[] */
	public function getFaces() : array
	{
		return $this->faces;
	}

	public function hasFace(int $face) : bool
	{
		return isset($this->faces[$face]);
	}

	/**
	 * @param int[] $faces
	 * @phpstan-param list<Facing::NORTH|Facing::EAST|Facing::SOUTH|Facing::WEST> $faces
	 * @return $this
	 */
	public function setFaces(array $faces) : self
	{
		$uniqueFaces = [];
		foreach ($faces as $face) {
			if ($face !== Facing::NORTH && $face !== Facing::SOUTH && $face !== Facing::WEST && $face !== Facing::EAST) {
				throw new \InvalidArgumentException("Facing can only be north, east, south or west");
			}
			$uniqueFaces[$face] = $face;
		}
		$this->faces = $uniqueFaces;
		return $this;
	}

	/** @return $this */
	public function setFace(int $face, bool $value) : self
	{
		if ($face !== Facing::NORTH && $face !== Facing::SOUTH && $face !== Facing::WEST && $face !== Facing::EAST) {
			throw new \InvalidArgumentException("Facing can only be north, east, south or west");
		}
		if ($value) {
			$this->faces[$face] = $face;
		} else {
			unset($this->faces[$face]);
		}
		return $this;
	}

	public function hasEntityCollision() : bool
	{
		return true;
	}

	public function canClimb() : bool
	{
		return true;
	}

	public function canBeReplaced() : bool
	{
		return true;
	}

	public function onEntityInside(Entity $entity) : bool
	{
		$entity->resetFallDistance();
		return true;
	}

	protected function recalculateCollisionBoxes() : array
	{
		return [];
	}

	public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool
	{
		if (!$blockReplace->getSide(Facing::opposite($face))->isFullCube() || Facing::axis($face) === Axis::Y) {
			return false;
		}

		$this->faces = $blockReplace instanceof Vine ? $blockReplace->faces : [];
		$this->faces[Facing::opposite($face)] = Facing::opposite($face);

		return parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player);
	}

	public function onNearbyBlockChange() : void
	{
		$changed = false;

		$up = $this->getSide(Facing::UP);
		//check which faces have corresponding vines in the block above
		$supportedFaces = $up instanceof Vine ? array_intersect_key($this->faces, $up->faces) : [];

		foreach ($this->faces as $face) {
			if (!isset($supportedFaces[$face]) && !$this->getSide($face)->isSolid()) {
				unset($this->faces[$face]);
				$changed = true;
			}
		}

		if ($changed) {
			$world = $this->position->getWorld();
			if (count($this->faces) === 0) {
				$world->useBreakOn($this->position);
			} else {
				$world->setBlock($this->position, $this);
			}
		}
	}

	public function ticksRandomly() : bool
	{
		return true;
	}

	public function onRandomTick() : void
	{
		//TODO: vine growth
	}

	public function getDrops(Item $item) : array
	{
		if (($item->getBlockToolType() & BlockToolType::SHEARS) !== 0) {
			return $this->getDropsForCompatibleTool($item);
		}

		return [];
	}

	public function getFlameEncouragement() : int
	{
		return 15;
	}

	public function getFlammability() : int
	{
		return 100;
	}
}
