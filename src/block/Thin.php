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

/**
 * Thin blocks behave like glass panes. They connect to full-cube blocks horizontally adjacent to them if possible.
 */
class Thin extends Transparent
{
	/** @var bool[] facing => dummy */
	protected array $connections = [];

	public function readStateFromWorld() : Block
	{
		parent::readStateFromWorld();

		$this->collisionBoxes = null;

		foreach (Facing::HORIZONTAL as $facing) {
			$side = $this->getSide($facing);
			if ($side instanceof Thin || $side instanceof Wall || $side->getSupportType(Facing::opposite($facing)) === SupportType::FULL) {
				$this->connections[$facing] = true;
			} else {
				unset($this->connections[$facing]);
			}
		}

		return $this;
	}

	protected function recalculateCollisionBoxes() : array
	{
		$inset = 7 / 16;

		/** @var AxisAlignedBB[] $bbs */
		$bbs = [];

		if (isset($this->connections[Facing::WEST]) || isset($this->connections[Facing::EAST])) {
			$bb = AxisAlignedBB::one()->squash(Axis::Z, $inset);

			if (!isset($this->connections[Facing::WEST])) {
				$bb->trim(Facing::WEST, $inset);
			} elseif (!isset($this->connections[Facing::EAST])) {
				$bb->trim(Facing::EAST, $inset);
			}
			$bbs[] = $bb;
		}

		if (isset($this->connections[Facing::NORTH]) || isset($this->connections[Facing::SOUTH])) {
			$bb = AxisAlignedBB::one()->squash(Axis::X, $inset);

			if (!isset($this->connections[Facing::NORTH])) {
				$bb->trim(Facing::NORTH, $inset);
			} elseif (!isset($this->connections[Facing::SOUTH])) {
				$bb->trim(Facing::SOUTH, $inset);
			}
			$bbs[] = $bb;
		}

		if (count($bbs) === 0) {
			//centre post AABB (only needed if not connected on any axis - other BBs overlapping will do this if any connections are made)
			return [
				AxisAlignedBB::one()->contract($inset, 0, $inset)
			];
		}

		return $bbs;
	}

	public function getSupportType(int $facing) : SupportType
	{
		return SupportType::NONE;
	}
}
