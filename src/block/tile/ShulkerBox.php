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

use watermossmc\block\inventory\ShulkerBoxInventory;
use watermossmc\item\Item;
use watermossmc\math\Facing;
use watermossmc\math\Vector3;
use watermossmc\nbt\tag\CompoundTag;
use watermossmc\world\World;

class ShulkerBox extends Spawnable implements Container, Nameable
{
	use NameableTrait {
		addAdditionalSpawnData as addNameSpawnData;
	}
	use ContainerTrait;

	public const TAG_FACING = "facing";

	protected int $facing = Facing::NORTH;

	protected ShulkerBoxInventory $inventory;

	public function __construct(World $world, Vector3 $pos)
	{
		parent::__construct($world, $pos);
		$this->inventory = new ShulkerBoxInventory($this->position);
	}

	public function readSaveData(CompoundTag $nbt) : void
	{
		$this->loadName($nbt);
		$this->loadItems($nbt);
		$this->facing = $nbt->getByte(self::TAG_FACING, $this->facing);
	}

	protected function writeSaveData(CompoundTag $nbt) : void
	{
		$this->saveName($nbt);
		$this->saveItems($nbt);
		$nbt->setByte(self::TAG_FACING, $this->facing);
	}

	public function copyDataFromItem(Item $item) : void
	{
		$this->readSaveData($item->getNamedTag());
		if ($item->hasCustomName()) {
			$this->setName($item->getCustomName());
		}
	}

	public function close() : void
	{
		if (!$this->closed) {
			$this->inventory->removeAllViewers();
			parent::close();
		}
	}

	protected function onBlockDestroyedHook() : void
	{
		//NOOP override of ContainerTrait - shulker boxes retain their contents when destroyed
	}

	public function getCleanedNBT() : ?CompoundTag
	{
		$nbt = parent::getCleanedNBT();
		if ($nbt !== null) {
			$nbt->removeTag(self::TAG_FACING);
		}
		return $nbt;
	}

	public function getFacing() : int
	{
		return $this->facing;
	}

	public function setFacing(int $facing) : void
	{
		$this->facing = $facing;
	}

	public function getInventory() : ShulkerBoxInventory
	{
		return $this->inventory;
	}

	public function getRealInventory() : ShulkerBoxInventory
	{
		return $this->inventory;
	}

	public function getDefaultName() : string
	{
		return "Shulker Box";
	}

	protected function addAdditionalSpawnData(CompoundTag $nbt) : void
	{
		$nbt->setByte(self::TAG_FACING, $this->facing);
		$this->addNameSpawnData($nbt);
	}
}
