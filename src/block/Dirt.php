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

use watermossmc\block\utils\DirtType;
use watermossmc\data\runtime\RuntimeDataDescriber;
use watermossmc\item\Fertilizer;
use watermossmc\item\Hoe;
use watermossmc\item\Item;
use watermossmc\item\Potion;
use watermossmc\item\PotionType;
use watermossmc\item\SplashPotion;
use watermossmc\math\Facing;
use watermossmc\math\Vector3;
use watermossmc\player\Player;
use watermossmc\world\sound\ItemUseOnBlockSound;
use watermossmc\world\sound\WaterSplashSound;

class Dirt extends Opaque
{
	protected DirtType $dirtType = DirtType::NORMAL;

	public function describeBlockItemState(RuntimeDataDescriber $w) : void
	{
		$w->enum($this->dirtType);
	}

	public function getDirtType() : DirtType
	{
		return $this->dirtType;
	}

	/** @return $this */
	public function setDirtType(DirtType $dirtType) : self
	{
		$this->dirtType = $dirtType;
		return $this;
	}

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null, array &$returnedItems = []) : bool
	{
		$world = $this->position->getWorld();
		if ($face !== Facing::DOWN && $item instanceof Hoe) {
			$up = $this->getSide(Facing::UP);
			if ($up->getTypeId() !== BlockTypeIds::AIR) {
				return true;
			}

			$item->applyDamage(1);

			$newBlock = $this->dirtType === DirtType::NORMAL ? VanillaBlocks::FARMLAND() : VanillaBlocks::DIRT();
			$center = $this->position->add(0.5, 0.5, 0.5);
			$world->addSound($center, new ItemUseOnBlockSound($newBlock));
			$world->setBlock($this->position, $newBlock);
			if ($this->dirtType === DirtType::ROOTED) {
				$world->dropItem($center, VanillaBlocks::HANGING_ROOTS()->asItem());
			}

			return true;
		} elseif ($this->dirtType === DirtType::ROOTED && $item instanceof Fertilizer) {
			$down = $this->getSide(Facing::DOWN);
			if ($down->getTypeId() !== BlockTypeIds::AIR) {
				return true;
			}

			$item->pop();
			$world->setBlock($down->position, VanillaBlocks::HANGING_ROOTS());
			//TODO: bonemeal particles, growth sounds
		} elseif (($item instanceof Potion || $item instanceof SplashPotion) && $item->getType() === PotionType::WATER) {
			$item->pop();
			$world->setBlock($this->position, VanillaBlocks::MUD());
			$world->addSound($this->position, new WaterSplashSound(0.5));
			return true;
		}

		return false;
	}
}
