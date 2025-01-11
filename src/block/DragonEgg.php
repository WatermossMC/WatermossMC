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

use watermossmc\block\utils\Fallable;
use watermossmc\block\utils\FallableTrait;
use watermossmc\block\utils\SupportType;
use watermossmc\event\block\BlockTeleportEvent;
use watermossmc\item\Item;
use watermossmc\math\Vector3;
use watermossmc\player\GameMode;
use watermossmc\player\Player;
use watermossmc\world\particle\DragonEggTeleportParticle;
use watermossmc\world\World;

use function max;
use function min;
use function mt_rand;

class DragonEgg extends Transparent implements Fallable
{
	use FallableTrait;

	public function getLightLevel() : int
	{
		return 1;
	}

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null, array &$returnedItems = []) : bool
	{
		$this->teleport();
		return true;
	}

	public function onAttack(Item $item, int $face, ?Player $player = null) : bool
	{
		if ($player !== null && $player->getGamemode() !== GameMode::CREATIVE) {
			$this->teleport();
			return true;
		}
		return false;
	}

	public function teleport() : void
	{
		$world = $this->position->getWorld();
		for ($tries = 0; $tries < 16; ++$tries) {
			$block = $world->getBlockAt(
				$this->position->x + mt_rand(-16, 16),
				max(World::Y_MIN, min(World::Y_MAX - 1, $this->position->y + mt_rand(-8, 8))),
				$this->position->z + mt_rand(-16, 16)
			);
			if ($block instanceof Air) {
				$ev = new BlockTeleportEvent($this, $block->position);
				$ev->call();
				if ($ev->isCancelled()) {
					break;
				}

				$blockPos = $ev->getTo();
				$world->addParticle($this->position, new DragonEggTeleportParticle($this->position->x - $blockPos->x, $this->position->y - $blockPos->y, $this->position->z - $blockPos->z));
				$world->setBlock($this->position, VanillaBlocks::AIR());
				$world->setBlock($blockPos, $this);
				break;
			}
		}
	}

	public function getSupportType(int $facing) : SupportType
	{
		return SupportType::NONE;
	}
}
