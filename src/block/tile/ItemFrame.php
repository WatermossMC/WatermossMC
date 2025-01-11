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

use watermossmc\item\Item;
use watermossmc\item\VanillaItems;
use watermossmc\math\Vector3;
use watermossmc\nbt\tag\CompoundTag;
use watermossmc\nbt\tag\FloatTag;
use watermossmc\network\mcpe\convert\TypeConverter;
use watermossmc\world\World;

/**
 * @deprecated
 * @see \watermossmc\block\ItemFrame
 */
class ItemFrame extends Spawnable
{
	public const TAG_ITEM_ROTATION = "ItemRotation";
	public const TAG_ITEM_DROP_CHANCE = "ItemDropChance";
	public const TAG_ITEM = "Item";

	private Item $item;
	private int $itemRotation = 0;
	private float $itemDropChance = 1.0;

	public function __construct(World $world, Vector3 $pos)
	{
		$this->item = VanillaItems::AIR();
		parent::__construct($world, $pos);
	}

	public function readSaveData(CompoundTag $nbt) : void
	{
		if (($itemTag = $nbt->getCompoundTag(self::TAG_ITEM)) !== null) {
			$this->item = Item::nbtDeserialize($itemTag);
		}
		if ($nbt->getTag(self::TAG_ITEM_ROTATION) instanceof FloatTag) {
			$this->itemRotation = (int) ($nbt->getFloat(self::TAG_ITEM_ROTATION, $this->itemRotation * 45) / 45);
		} else {
			$this->itemRotation = $nbt->getByte(self::TAG_ITEM_ROTATION, $this->itemRotation);
		}
		$this->itemDropChance = $nbt->getFloat(self::TAG_ITEM_DROP_CHANCE, $this->itemDropChance);
	}

	protected function writeSaveData(CompoundTag $nbt) : void
	{
		$nbt->setFloat(self::TAG_ITEM_DROP_CHANCE, $this->itemDropChance);
		$nbt->setFloat(self::TAG_ITEM_ROTATION, $this->itemRotation * 45);
		if (!$this->item->isNull()) {
			$nbt->setTag(self::TAG_ITEM, $this->item->nbtSerialize());
		}
	}

	public function hasItem() : bool
	{
		return !$this->item->isNull();
	}

	public function getItem() : Item
	{
		return clone $this->item;
	}

	public function setItem(?Item $item) : void
	{
		if ($item !== null && !$item->isNull()) {
			$this->item = clone $item;
		} else {
			$this->item = VanillaItems::AIR();
		}
	}

	public function getItemRotation() : int
	{
		return $this->itemRotation;
	}

	public function setItemRotation(int $rotation) : void
	{
		$this->itemRotation = $rotation;
	}

	public function getItemDropChance() : float
	{
		return $this->itemDropChance;
	}

	public function setItemDropChance(float $chance) : void
	{
		$this->itemDropChance = $chance;
	}

	protected function addAdditionalSpawnData(CompoundTag $nbt) : void
	{
		$nbt->setFloat(self::TAG_ITEM_DROP_CHANCE, $this->itemDropChance);
		$nbt->setFloat(self::TAG_ITEM_ROTATION, $this->itemRotation * 45);
		if (!$this->item->isNull()) {
			$nbt->setTag(self::TAG_ITEM, TypeConverter::getInstance()->getItemTranslator()->toNetworkNbt($this->item));
		}
	}
}
