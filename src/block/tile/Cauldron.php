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

use watermossmc\block\Block;
use watermossmc\block\FillableCauldron;
use watermossmc\color\Color;
use watermossmc\data\bedrock\block\BlockStateNames;
use watermossmc\data\bedrock\PotionTypeIdMap;
use watermossmc\data\SavedDataLoadingException;
use watermossmc\item\Item;
use watermossmc\item\ItemTypeIds;
use watermossmc\item\Potion;
use watermossmc\item\SplashPotion;
use watermossmc\item\VanillaItems;
use watermossmc\nbt\tag\CompoundTag;
use watermossmc\nbt\tag\IntTag;
use watermossmc\utils\AssumptionFailedError;
use watermossmc\utils\Binary;

final class Cauldron extends Spawnable
{
	private const POTION_CONTAINER_TYPE_NONE = -1;
	private const POTION_CONTAINER_TYPE_NORMAL = 0;
	private const POTION_CONTAINER_TYPE_SPLASH = 1;
	private const POTION_CONTAINER_TYPE_LINGERING = 2;

	private const POTION_ID_NONE = -1;

	private const TAG_POTION_ID = "PotionId"; //TAG_Short
	private const TAG_POTION_CONTAINER_TYPE = "PotionType"; //TAG_Short
	private const TAG_CUSTOM_COLOR = "CustomColor"; //TAG_Int

	private ?Item $potionItem = null;
	private ?Color $customWaterColor = null;

	public function getPotionItem() : ?Item
	{
		return $this->potionItem;
	}

	public function setPotionItem(?Item $potionItem) : void
	{
		$this->potionItem = $potionItem;
	}

	public function getCustomWaterColor() : ?Color
	{
		return $this->customWaterColor;
	}

	public function setCustomWaterColor(?Color $customWaterColor) : void
	{
		$this->customWaterColor = $customWaterColor;
	}

	protected function addAdditionalSpawnData(CompoundTag $nbt) : void
	{
		$nbt->setShort(self::TAG_POTION_CONTAINER_TYPE, match($this->potionItem?->getTypeId()) {
			ItemTypeIds::POTION => self::POTION_CONTAINER_TYPE_NORMAL,
			ItemTypeIds::SPLASH_POTION => self::POTION_CONTAINER_TYPE_SPLASH,
			ItemTypeIds::LINGERING_POTION => self::POTION_CONTAINER_TYPE_LINGERING,
			null => self::POTION_CONTAINER_TYPE_NONE,
			default => throw new AssumptionFailedError("Unexpected potion item type")
		});

		//TODO: lingering potion
		$type = $this->potionItem instanceof Potion || $this->potionItem instanceof SplashPotion ? $this->potionItem->getType() : null;
		$nbt->setShort(self::TAG_POTION_ID, $type === null ? self::POTION_ID_NONE : PotionTypeIdMap::getInstance()->toId($type));

		if ($this->customWaterColor !== null) {
			$nbt->setInt(self::TAG_CUSTOM_COLOR, Binary::signInt($this->customWaterColor->toARGB()));
		}
	}

	public function readSaveData(CompoundTag $nbt) : void
	{
		$containerType = $nbt->getShort(self::TAG_POTION_CONTAINER_TYPE, self::POTION_CONTAINER_TYPE_NONE);
		$potionId = $nbt->getShort(self::TAG_POTION_ID, self::POTION_ID_NONE);
		if ($containerType !== self::POTION_CONTAINER_TYPE_NONE && $potionId !== self::POTION_ID_NONE) {
			$potionType = PotionTypeIdMap::getInstance()->fromId($potionId);
			if ($potionType === null) {
				throw new SavedDataLoadingException("Unknown potion type ID $potionId");
			}
			$this->potionItem = match($containerType) {
				self::POTION_CONTAINER_TYPE_NORMAL => VanillaItems::POTION()->setType($potionType),
				self::POTION_CONTAINER_TYPE_SPLASH => VanillaItems::SPLASH_POTION()->setType($potionType),
				self::POTION_CONTAINER_TYPE_LINGERING => throw new SavedDataLoadingException("Not implemented"),
				default => throw new SavedDataLoadingException("Invalid potion container type ID $containerType")
			};
		} else {
			$this->potionItem = null;
		}

		$this->customWaterColor = ($customColorTag = $nbt->getTag(self::TAG_CUSTOM_COLOR)) instanceof IntTag ? Color::fromARGB(Binary::unsignInt($customColorTag->getValue())) : null;
	}

	protected function writeSaveData(CompoundTag $nbt) : void
	{
		$nbt->setShort(self::TAG_POTION_CONTAINER_TYPE, match($this->potionItem?->getTypeId()) {
			ItemTypeIds::POTION => self::POTION_CONTAINER_TYPE_NORMAL,
			ItemTypeIds::SPLASH_POTION => self::POTION_CONTAINER_TYPE_SPLASH,
			ItemTypeIds::LINGERING_POTION => self::POTION_CONTAINER_TYPE_LINGERING,
			null => self::POTION_CONTAINER_TYPE_NONE,
			default => throw new AssumptionFailedError("Unexpected potion item type")
		});

		//TODO: lingering potion
		$type = $this->potionItem instanceof Potion || $this->potionItem instanceof SplashPotion ? $this->potionItem->getType() : null;
		$nbt->setShort(self::TAG_POTION_ID, $type === null ? self::POTION_ID_NONE : PotionTypeIdMap::getInstance()->toId($type));

		if ($this->customWaterColor !== null) {
			$nbt->setInt(self::TAG_CUSTOM_COLOR, Binary::signInt($this->customWaterColor->toARGB()));
		}
	}

	public function getRenderUpdateBugWorkaroundStateProperties(Block $block) : array
	{
		if ($block instanceof FillableCauldron) {
			$realFillLevel = $block->getFillLevel();
			return [BlockStateNames::FILL_LEVEL => new IntTag($realFillLevel === FillableCauldron::MAX_FILL_LEVEL ? FillableCauldron::MIN_FILL_LEVEL : $realFillLevel + 1)];
		}

		return [];
	}
}
