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
use watermossmc\math\AxisAlignedBB;
use watermossmc\math\Vector3;

/**
 * "Flowable" blocks are destroyed if water flows into the same space as the block. These blocks usually don't have any
 * collision boxes, and can't provide support for other blocks.
 */
abstract class Flowable extends Transparent
{
	public function canBeFlowedInto() : bool
	{
		return true;
	}

	public function isSolid() : bool
	{
		return false;
	}

	public function canBePlacedAt(Block $blockReplace, Vector3 $clickVector, int $face, bool $isClickedBlock) : bool
	{
		return (!$this->canBeFlowedInto() || !$blockReplace instanceof Liquid) &&
			parent::canBePlacedAt($blockReplace, $clickVector, $face, $isClickedBlock);
	}

	/**
	 * @return AxisAlignedBB[]
	 */
	protected function recalculateCollisionBoxes() : array
	{
		return [];
	}

	public function getSupportType(int $facing) : SupportType
	{
		return SupportType::NONE;
	}
}
