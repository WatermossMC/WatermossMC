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

use watermossmc\block\tile\FlowerPot as TileFlowerPot;
use watermossmc\block\utils\StaticSupportTrait;
use watermossmc\item\Item;
use watermossmc\math\AxisAlignedBB;
use watermossmc\math\Facing;
use watermossmc\math\Vector3;
use watermossmc\player\Player;

use function assert;

class FlowerPot extends Flowable
{
	use StaticSupportTrait;

	protected ?Block $plant = null;

	public function readStateFromWorld() : Block
	{
		parent::readStateFromWorld();
		$tile = $this->position->getWorld()->getTile($this->position);
		if ($tile instanceof TileFlowerPot) {
			$this->setPlant($tile->getPlant());
		} else {
			$this->setPlant(null);
		}

		return $this;
	}

	public function writeStateToWorld() : void
	{
		parent::writeStateToWorld();

		$tile = $this->position->getWorld()->getTile($this->position);
		assert($tile instanceof TileFlowerPot);
		$tile->setPlant($this->plant);
	}

	public function getPlant() : ?Block
	{
		return $this->plant;
	}

	/** @return $this */
	public function setPlant(?Block $plant) : self
	{
		if ($plant === null || $plant instanceof Air) {
			$this->plant = null;
		} else {
			$this->plant = clone $plant;
		}
		return $this;
	}

	public function canAddPlant(Block $block) : bool
	{
		if ($this->plant !== null) {
			return false;
		}

		return $this->isValidPlant($block);
	}

	private function isValidPlant(Block $block) : bool
	{
		return $block->hasTypeTag(BlockTypeTags::POTTABLE_PLANTS);
	}

	/**
	 * @return AxisAlignedBB[]
	 */
	protected function recalculateCollisionBoxes() : array
	{
		return [AxisAlignedBB::one()->contract(3 / 16, 0, 3 / 16)->trim(Facing::UP, 5 / 8)];
	}

	private function canBeSupportedAt(Block $block) : bool
	{
		return $block->getAdjacentSupportType(Facing::DOWN)->hasCenterSupport();
	}

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null, array &$returnedItems = []) : bool
	{
		$world = $this->position->getWorld();
		$plant = $item->getBlock();
		if ($this->plant !== null) {
			if ($this->isValidPlant($plant)) {
				//for some reason, vanilla doesn't remove the contents of the pot if the held item is plantable
				//and will also cause a new plant to be placed if clicking on the side
				return false;
			}

			$removedItems = [$this->plant->asItem()];
			if ($player !== null) {
				//this one just has to be a weirdo :(
				//this is the only block that directly adds items to the player inventory instead of just dropping items
				$removedItems = $player->getInventory()->addItem(...$removedItems);
			}
			foreach ($removedItems as $drops) {
				$world->dropItem($this->position->add(0.5, 0.5, 0.5), $drops);
			}

			$this->setPlant(null);
			$world->setBlock($this->position, $this);
			return true;
		} elseif ($this->isValidPlant($plant)) {
			$this->setPlant($plant);
			$item->pop();
			$world->setBlock($this->position, $this);

			return true;
		}

		return false;
	}

	public function getDropsForCompatibleTool(Item $item) : array
	{
		$items = parent::getDropsForCompatibleTool($item);
		if ($this->plant !== null) {
			$items[] = $this->plant->asItem();
		}

		return $items;
	}

	public function getPickedItem(bool $addUserData = false) : Item
	{
		return $this->plant !== null ? $this->plant->asItem() : parent::getPickedItem($addUserData);
	}
}
