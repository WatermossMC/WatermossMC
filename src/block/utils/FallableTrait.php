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

namespace watermossmc\block\utils;

use watermossmc\block\Block;
use watermossmc\block\VanillaBlocks;
use watermossmc\entity\Location;
use watermossmc\entity\object\FallingBlock;
use watermossmc\math\Facing;
use watermossmc\utils\AssumptionFailedError;
use watermossmc\world\Position;
use watermossmc\world\sound\Sound;

/**
 * This trait handles falling behaviour for blocks that need them.
 * TODO: convert this into a dynamic component
 * @see Fallable
 */
trait FallableTrait
{
	abstract protected function getPosition() : Position;

	public function onNearbyBlockChange() : void
	{
		$pos = $this->getPosition();
		$world = $pos->getWorld();
		$down = $world->getBlock($pos->getSide(Facing::DOWN));
		if ($down->canBeReplaced()) {
			$world->setBlock($pos, VanillaBlocks::AIR());

			$block = $this;
			if (!($block instanceof Block)) {
				throw new AssumptionFailedError(__TRAIT__ . " should only be used by Blocks");
			}

			$fall = new FallingBlock(Location::fromObject($pos->add(0.5, 0, 0.5), $world), $block);
			$fall->spawnToAll();
		}
	}

	public function tickFalling() : ?Block
	{
		return null;
	}

	public function onHitGround(FallingBlock $blockEntity) : bool
	{
		return true;
	}

	public function getFallDamagePerBlock() : float
	{
		return 0.0;
	}

	public function getMaxFallDamage() : float
	{
		return 0.0;
	}

	public function getLandSound() : ?Sound
	{
		return null;
	}
}
