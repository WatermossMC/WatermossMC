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
use watermossmc\entity\EntitySizeInfo;
use watermossmc\math\RayTraceResult;

abstract class Throwable extends Projectile
{
	protected function getInitialSizeInfo() : EntitySizeInfo
	{
		return new EntitySizeInfo(0.25, 0.25);
	}

	protected function getInitialDragMultiplier() : float
	{
		return 0.01;
	}

	protected function getInitialGravity() : float
	{
		return 0.03;
	}

	protected function onHitBlock(Block $blockHit, RayTraceResult $hitResult) : void
	{
		parent::onHitBlock($blockHit, $hitResult);
		$this->flagForDespawn();
	}
}
