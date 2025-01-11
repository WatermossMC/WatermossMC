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

use watermossmc\block\utils\PillarRotationTrait;
use watermossmc\block\utils\SupportType;
use watermossmc\math\Axis;
use watermossmc\math\AxisAlignedBB;
use watermossmc\math\Facing;

final class Chain extends Transparent
{
	use PillarRotationTrait;

	public function getSupportType(int $facing) : SupportType
	{
		return $this->axis === Axis::Y && Facing::axis($facing) === Axis::Y ? SupportType::CENTER : SupportType::NONE;
	}

	protected function recalculateCollisionBoxes() : array
	{
		$bb = AxisAlignedBB::one();
		foreach ([Axis::Y, Axis::Z, Axis::X] as $axis) {
			if ($axis !== $this->axis) {
				$bb->squash($axis, 13 / 32);
			}
		}
		return [$bb];
	}
}
