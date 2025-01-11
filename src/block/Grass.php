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

use watermossmc\block\utils\BlockEventHelper;
use watermossmc\block\utils\DirtType;
use watermossmc\item\Fertilizer;
use watermossmc\item\Hoe;
use watermossmc\item\Item;
use watermossmc\item\Shovel;
use watermossmc\math\Facing;
use watermossmc\math\Vector3;
use watermossmc\player\Player;
use watermossmc\utils\Random;
use watermossmc\world\generator\object\TallGrass as TallGrassObject;
use watermossmc\world\sound\ItemUseOnBlockSound;

use function mt_rand;

class Grass extends Opaque
{
	public function getDropsForCompatibleTool(Item $item) : array
	{
		return [
			VanillaBlocks::DIRT()->asItem()
		];
	}

	public function isAffectedBySilkTouch() : bool
	{
		return true;
	}

	public function ticksRandomly() : bool
	{
		return true;
	}

	public function onRandomTick() : void
	{
		$world = $this->position->getWorld();
		$lightAbove = $world->getFullLightAt($this->position->x, $this->position->y + 1, $this->position->z);
		if ($lightAbove < 4 && $world->getBlockAt($this->position->x, $this->position->y + 1, $this->position->z)->getLightFilter() >= 2) {
			//grass dies
			BlockEventHelper::spread($this, VanillaBlocks::DIRT(), $this);
		} elseif ($lightAbove >= 9) {
			//try grass spread
			for ($i = 0; $i < 4; ++$i) {
				$x = mt_rand($this->position->x - 1, $this->position->x + 1);
				$y = mt_rand($this->position->y - 3, $this->position->y + 1);
				$z = mt_rand($this->position->z - 1, $this->position->z + 1);

				$b = $world->getBlockAt($x, $y, $z);
				if (
					!($b instanceof Dirt) ||
					$b->getDirtType() !== DirtType::NORMAL ||
					$world->getFullLightAt($x, $y + 1, $z) < 4 ||
					$world->getBlockAt($x, $y + 1, $z)->getLightFilter() >= 2
				) {
					continue;
				}

				BlockEventHelper::spread($b, VanillaBlocks::GRASS(), $this);
			}
		}
	}

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null, array &$returnedItems = []) : bool
	{
		if ($this->getSide(Facing::UP)->getTypeId() !== BlockTypeIds::AIR) {
			return false;
		}
		$world = $this->position->getWorld();
		if ($item instanceof Fertilizer) {
			$item->pop();
			TallGrassObject::growGrass($world, $this->position, new Random(mt_rand()), 8, 2);

			return true;
		}
		if ($face !== Facing::DOWN) {
			if ($item instanceof Hoe) {
				$item->applyDamage(1);
				$newBlock = VanillaBlocks::FARMLAND();
				$world->addSound($this->position->add(0.5, 0.5, 0.5), new ItemUseOnBlockSound($newBlock));
				$world->setBlock($this->position, $newBlock);

				return true;
			} elseif ($item instanceof Shovel) {
				$item->applyDamage(1);
				$newBlock = VanillaBlocks::GRASS_PATH();
				$world->addSound($this->position->add(0.5, 0.5, 0.5), new ItemUseOnBlockSound($newBlock));
				$world->setBlock($this->position, $newBlock);

				return true;
			}
		}

		return false;
	}
}
