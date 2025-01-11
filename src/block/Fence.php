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

use watermossmc\block\utils\SupportType;
use watermossmc\math\Axis;
use watermossmc\math\AxisAlignedBB;
use watermossmc\math\Facing;

use function count;

class Fence extends Transparent
{
	/** @var bool[] facing => dummy */
	protected array $connections = [];

	public function getThickness() : float
	{
		return 0.25;
	}

	public function readStateFromWorld() : Block
	{
		parent::readStateFromWorld();

		$this->collisionBoxes = null;

		foreach (Facing::HORIZONTAL as $facing) {
			$block = $this->getSide($facing);
			if ($block instanceof static || $block instanceof FenceGate || $block->getSupportType(Facing::opposite($facing)) === SupportType::FULL) {
				$this->connections[$facing] = true;
			} else {
				unset($this->connections[$facing]);
			}
		}

		return $this;
	}

	/**
	 * @return AxisAlignedBB[]
	 */
	protected function recalculateCollisionBoxes() : array
	{
		$inset = 0.5 - $this->getThickness() / 2;

		/** @var AxisAlignedBB[] $bbs */
		$bbs = [];

		$connectWest = isset($this->connections[Facing::WEST]);
		$connectEast = isset($this->connections[Facing::EAST]);

		if ($connectWest || $connectEast) {
			//X axis (west/east)
			$bbs[] = AxisAlignedBB::one()
				->squash(Axis::Z, $inset)
				->extend(Facing::UP, 0.5)
				->trim(Facing::WEST, $connectWest ? 0 : $inset)
				->trim(Facing::EAST, $connectEast ? 0 : $inset);
		}

		$connectNorth = isset($this->connections[Facing::NORTH]);
		$connectSouth = isset($this->connections[Facing::SOUTH]);

		if ($connectNorth || $connectSouth) {
			//Z axis (north/south)
			$bbs[] = AxisAlignedBB::one()
				->squash(Axis::X, $inset)
				->extend(Facing::UP, 0.5)
				->trim(Facing::NORTH, $connectNorth ? 0 : $inset)
				->trim(Facing::SOUTH, $connectSouth ? 0 : $inset);
		}

		if (count($bbs) === 0) {
			//centre post AABB (only needed if not connected on any axis - other BBs overlapping will do this if any connections are made)
			return [
				AxisAlignedBB::one()
					->extend(Facing::UP, 0.5)
					->contract($inset, 0, $inset)
			];
		}

		return $bbs;
	}

	public function getSupportType(int $facing) : SupportType
	{
		return Facing::axis($facing) === Axis::Y ? SupportType::CENTER : SupportType::NONE;
	}
}
