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

use watermossmc\math\Facing;

final class SoulFire extends BaseFire
{
	public function getLightLevel() : int
	{
		return 10;
	}

	protected function getFireDamage() : int
	{
		return 2;
	}

	public static function canBeSupportedBy(Block $block) : bool
	{
		//TODO: this really ought to use some kind of tag system
		$id = $block->getTypeId();
		return $id === BlockTypeIds::SOUL_SAND || $id === BlockTypeIds::SOUL_SOIL;
	}

	public function onNearbyBlockChange() : void
	{
		if (!self::canBeSupportedBy($this->getSide(Facing::DOWN))) {
			$this->position->getWorld()->setBlock($this->position, VanillaBlocks::AIR());
		}
	}
}
