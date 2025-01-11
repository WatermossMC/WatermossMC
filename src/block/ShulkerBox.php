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

use watermossmc\block\tile\ShulkerBox as TileShulkerBox;
use watermossmc\block\utils\AnyFacingTrait;
use watermossmc\block\utils\SupportType;
use watermossmc\data\runtime\RuntimeDataDescriber;
use watermossmc\item\Item;
use watermossmc\math\Vector3;
use watermossmc\player\Player;
use watermossmc\world\BlockTransaction;

class ShulkerBox extends Opaque
{
	use AnyFacingTrait;

	protected function describeBlockOnlyState(RuntimeDataDescriber $w) : void
	{
		//NOOP - we don't read or write facing here, because the tile persists it
	}

	public function writeStateToWorld() : void
	{
		parent::writeStateToWorld();
		$shulker = $this->position->getWorld()->getTile($this->position);
		if ($shulker instanceof TileShulkerBox) {
			$shulker->setFacing($this->facing);
		}
	}

	public function readStateFromWorld() : Block
	{
		parent::readStateFromWorld();
		$shulker = $this->position->getWorld()->getTile($this->position);
		if ($shulker instanceof TileShulkerBox) {
			$this->facing = $shulker->getFacing();
		}

		return $this;
	}

	public function getMaxStackSize() : int
	{
		return 1;
	}

	public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool
	{
		$this->facing = $face;

		return parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player);
	}

	private function addDataFromTile(TileShulkerBox $tile, Item $item) : void
	{
		$shulkerNBT = $tile->getCleanedNBT();
		if ($shulkerNBT !== null) {
			$item->setNamedTag($shulkerNBT);
		}
		if ($tile->hasName()) {
			$item->setCustomName($tile->getName());
		}
	}

	public function getDropsForCompatibleTool(Item $item) : array
	{
		$drop = $this->asItem();
		if (($tile = $this->position->getWorld()->getTile($this->position)) instanceof TileShulkerBox) {
			$this->addDataFromTile($tile, $drop);
		}
		return [$drop];
	}

	public function getPickedItem(bool $addUserData = false) : Item
	{
		$result = parent::getPickedItem($addUserData);
		if ($addUserData && ($tile = $this->position->getWorld()->getTile($this->position)) instanceof TileShulkerBox) {
			$this->addDataFromTile($tile, $result);
		}
		return $result;
	}

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null, array &$returnedItems = []) : bool
	{
		if ($player instanceof Player) {

			$shulker = $this->position->getWorld()->getTile($this->position);
			if ($shulker instanceof TileShulkerBox) {
				if (
					$this->getSide($this->facing)->isSolid() ||
					!$shulker->canOpenWith($item->getCustomName())
				) {
					return true;
				}

				$player->setCurrentWindow($shulker->getInventory());
			}
		}

		return true;
	}

	public function getSupportType(int $facing) : SupportType
	{
		return SupportType::NONE;
	}
}
