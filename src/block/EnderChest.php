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

use watermossmc\block\inventory\EnderChestInventory;
use watermossmc\block\tile\EnderChest as TileEnderChest;
use watermossmc\block\utils\FacesOppositePlacingPlayerTrait;
use watermossmc\block\utils\SupportType;
use watermossmc\item\Item;
use watermossmc\math\AxisAlignedBB;
use watermossmc\math\Facing;
use watermossmc\math\Vector3;
use watermossmc\player\Player;

class EnderChest extends Transparent
{
	use FacesOppositePlacingPlayerTrait;

	public function getLightLevel() : int
	{
		return 7;
	}

	/**
	 * @return AxisAlignedBB[]
	 */
	protected function recalculateCollisionBoxes() : array
	{
		//these are slightly bigger than in PC
		return [AxisAlignedBB::one()->contract(0.025, 0, 0.025)->trim(Facing::UP, 0.05)];
	}

	public function getSupportType(int $facing) : SupportType
	{
		return SupportType::NONE;
	}

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null, array &$returnedItems = []) : bool
	{
		if ($player instanceof Player) {
			$enderChest = $this->position->getWorld()->getTile($this->position);
			if ($enderChest instanceof TileEnderChest && $this->getSide(Facing::UP)->isTransparent()) {
				$enderChest->setViewerCount($enderChest->getViewerCount() + 1);
				$player->setCurrentWindow(new EnderChestInventory($this->position, $player->getEnderInventory()));
			}
		}

		return true;
	}

	public function getDropsForCompatibleTool(Item $item) : array
	{
		return [
			VanillaBlocks::OBSIDIAN()->asItem()->setCount(8)
		];
	}

	public function isAffectedBySilkTouch() : bool
	{
		return true;
	}
}
