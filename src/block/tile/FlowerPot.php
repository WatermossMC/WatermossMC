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

use watermossmc\block\Air;
use watermossmc\block\Block;
use watermossmc\block\RuntimeBlockStateRegistry;
use watermossmc\data\bedrock\block\BlockStateDeserializeException;
use watermossmc\data\bedrock\block\BlockStateNames;
use watermossmc\data\SavedDataLoadingException;
use watermossmc\nbt\tag\ByteTag;
use watermossmc\nbt\tag\CompoundTag;
use watermossmc\nbt\tag\IntTag;
use watermossmc\nbt\tag\ShortTag;
use watermossmc\network\mcpe\convert\TypeConverter;
use watermossmc\world\format\io\GlobalBlockStateHandlers;

/**
 * @deprecated
 * @see \watermossmc\block\FlowerPot
 */
class FlowerPot extends Spawnable
{
	private const TAG_ITEM = "item";
	private const TAG_ITEM_DATA = "mData";
	private const TAG_PLANT_BLOCK = "PlantBlock";

	private ?Block $plant = null;

	public function readSaveData(CompoundTag $nbt) : void
	{
		$blockStateData = null;

		$blockDataUpgrader = GlobalBlockStateHandlers::getUpgrader();
		if (($itemIdTag = $nbt->getTag(self::TAG_ITEM)) instanceof ShortTag && ($itemMetaTag = $nbt->getTag(self::TAG_ITEM_DATA)) instanceof IntTag) {
			try {
				$blockStateData = $blockDataUpgrader->upgradeIntIdMeta($itemIdTag->getValue(), $itemMetaTag->getValue());
			} catch (BlockStateDeserializeException $e) {
				throw new SavedDataLoadingException("Error loading legacy flower pot item data: " . $e->getMessage(), 0, $e);
			}
		} elseif (($plantBlockTag = $nbt->getCompoundTag(self::TAG_PLANT_BLOCK)) !== null) {
			try {
				$blockStateData = $blockDataUpgrader->upgradeBlockStateNbt($plantBlockTag);
			} catch (BlockStateDeserializeException $e) {
				throw new SavedDataLoadingException("Error loading " . self::TAG_PLANT_BLOCK . " tag for flower pot: " . $e->getMessage(), 0, $e);
			}
		}

		if ($blockStateData !== null) {
			try {
				$blockStateId = GlobalBlockStateHandlers::getDeserializer()->deserialize($blockStateData);
			} catch (BlockStateDeserializeException $e) {
				throw new SavedDataLoadingException("Error deserializing plant for flower pot: " . $e->getMessage(), 0, $e);
			}
			$this->setPlant(RuntimeBlockStateRegistry::getInstance()->fromStateId($blockStateId));
		}
	}

	protected function writeSaveData(CompoundTag $nbt) : void
	{
		if ($this->plant !== null) {
			$nbt->setTag(self::TAG_PLANT_BLOCK, GlobalBlockStateHandlers::getSerializer()->serialize($this->plant->getStateId())->toNbt());
		}
	}

	public function getPlant() : ?Block
	{
		return $this->plant !== null ? clone $this->plant : null;
	}

	public function setPlant(?Block $plant) : void
	{
		if ($plant === null || $plant instanceof Air) {
			$this->plant = null;
		} else {
			$this->plant = clone $plant;
		}
	}

	protected function addAdditionalSpawnData(CompoundTag $nbt) : void
	{
		if ($this->plant !== null) {
			$nbt->setTag(self::TAG_PLANT_BLOCK, TypeConverter::getInstance()->getBlockTranslator()->internalIdToNetworkStateData($this->plant->getStateId())->toNbt());
		}
	}

	public function getRenderUpdateBugWorkaroundStateProperties(Block $block) : array
	{
		return [BlockStateNames::UPDATE_BIT => new ByteTag(1)];
	}
}
