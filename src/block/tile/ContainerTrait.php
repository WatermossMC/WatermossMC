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

namespace watermossmc\block\tile;

use watermossmc\data\bedrock\item\SavedItemStackData;
use watermossmc\data\SavedDataLoadingException;
use watermossmc\inventory\Inventory;
use watermossmc\item\Item;
use watermossmc\nbt\NBT;
use watermossmc\nbt\tag\CompoundTag;
use watermossmc\nbt\tag\ListTag;
use watermossmc\nbt\tag\StringTag;
use watermossmc\world\Position;

/**
 * This trait implements most methods in the {@link Container} interface. It should only be used by Tiles.
 */
trait ContainerTrait
{
	/** @var string|null */
	private $lock = null;

	abstract public function getRealInventory() : Inventory;

	protected function loadItems(CompoundTag $tag) : void
	{
		if (($inventoryTag = $tag->getTag(Container::TAG_ITEMS)) instanceof ListTag && $inventoryTag->getTagType() === NBT::TAG_Compound) {
			$inventory = $this->getRealInventory();
			$listeners = $inventory->getListeners()->toArray();
			$inventory->getListeners()->remove(...$listeners); //prevent any events being fired by initialization

			$newContents = [];
			/** @var CompoundTag $itemNBT */
			foreach ($inventoryTag as $itemNBT) {
				try {
					$newContents[$itemNBT->getByte(SavedItemStackData::TAG_SLOT)] = Item::nbtDeserialize($itemNBT);
				} catch (SavedDataLoadingException $e) {
					//TODO: not the best solution
					\GlobalLogger::get()->logException($e);
					continue;
				}
			}
			$inventory->setContents($newContents);

			$inventory->getListeners()->add(...$listeners);
		}

		if (($lockTag = $tag->getTag(Container::TAG_LOCK)) instanceof StringTag) {
			$this->lock = $lockTag->getValue();
		}
	}

	protected function saveItems(CompoundTag $tag) : void
	{
		$items = [];
		foreach ($this->getRealInventory()->getContents() as $slot => $item) {
			$items[] = $item->nbtSerialize($slot);
		}

		$tag->setTag(Container::TAG_ITEMS, new ListTag($items, NBT::TAG_Compound));

		if ($this->lock !== null) {
			$tag->setString(Container::TAG_LOCK, $this->lock);
		}
	}

	/**
	 * @see Container::canOpenWith()
	 */
	public function canOpenWith(string $key) : bool
	{
		return $this->lock === null || $this->lock === $key;
	}

	/**
	 * @see Position::asPosition()
	 */
	abstract protected function getPosition() : Position;

	/**
	 * @see Tile::onBlockDestroyedHook()
	 */
	protected function onBlockDestroyedHook() : void
	{
		$inv = $this->getRealInventory();
		$pos = $this->getPosition();

		$world = $pos->getWorld();
		$dropPos = $pos->add(0.5, 0.5, 0.5);
		foreach ($inv->getContents() as $k => $item) {
			$world->dropItem($dropPos, $item);
		}
		$inv->clearAll();
	}
}
