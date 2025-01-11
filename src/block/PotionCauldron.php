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

use watermossmc\block\tile\Cauldron as TileCauldron;
use watermossmc\item\Item;
use watermossmc\item\ItemTypeIds;
use watermossmc\item\VanillaItems;
use watermossmc\math\Vector3;
use watermossmc\player\Player;
use watermossmc\world\sound\CauldronEmptyPotionSound;
use watermossmc\world\sound\CauldronFillPotionSound;
use watermossmc\world\sound\Sound;

use function assert;

final class PotionCauldron extends FillableCauldron
{
	public const POTION_FILL_AMOUNT = 2;

	private ?Item $potionItem = null;

	public function __construct(BlockIdentifier $idInfo, string $name, BlockTypeInfo $typeInfo)
	{
		parent::__construct($idInfo, $name, $typeInfo);
	}

	public function readStateFromWorld() : Block
	{
		parent::readStateFromWorld();
		$tile = $this->position->getWorld()->getTile($this->position);
		$this->potionItem = $tile instanceof TileCauldron ? $tile->getPotionItem() : null;

		return $this;
	}

	public function writeStateToWorld() : void
	{
		parent::writeStateToWorld();
		$tile = $this->position->getWorld()->getTile($this->position);
		assert($tile instanceof TileCauldron);
		$tile->setCustomWaterColor(null);
		$tile->setPotionItem($this->potionItem);
	}

	public function getPotionItem() : ?Item
	{
		return $this->potionItem === null ? null : clone $this->potionItem;
	}

	/** @return $this */
	public function setPotionItem(?Item $potionItem) : self
	{
		if ($potionItem !== null && !match($potionItem->getTypeId()) {
			ItemTypeIds::POTION,
			ItemTypeIds::SPLASH_POTION,
			ItemTypeIds::LINGERING_POTION => true,
			default => false,
		}) {
			throw new \InvalidArgumentException("Item must be a POTION, SPLASH_POTION or LINGERING_POTION");
		}
		$this->potionItem = $potionItem !== null ? (clone $potionItem)->setCount(1) : null;
		return $this;
	}

	public function getFillSound() : Sound
	{
		return new CauldronFillPotionSound();
	}

	public function getEmptySound() : Sound
	{
		return new CauldronEmptyPotionSound();
	}

	/**
	 * @param Item[] &$returnedItems
	 */
	protected function addFillLevelsOrMix(int $amount, Item $usedItem, Item $returnedItem, array &$returnedItems) : void
	{
		if ($this->potionItem !== null && !$usedItem->equals($this->potionItem, true, false)) {
			$this->mix($usedItem, $returnedItem, $returnedItems);
		} else {
			$this->addFillLevels($amount, $usedItem, $returnedItem, $returnedItems);
		}
	}

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null, array &$returnedItems = []) : bool
	{
		match($item->getTypeId()) {
			ItemTypeIds::LINGERING_POTION, ItemTypeIds::POTION, ItemTypeIds::SPLASH_POTION => $this->addFillLevelsOrMix(self::POTION_FILL_AMOUNT, $item, VanillaItems::GLASS_BOTTLE(), $returnedItems),
			ItemTypeIds::GLASS_BOTTLE => $this->potionItem === null ? null : $this->removeFillLevels(self::POTION_FILL_AMOUNT, $item, clone $this->potionItem, $returnedItems),
			ItemTypeIds::LAVA_BUCKET, ItemTypeIds::POWDER_SNOW_BUCKET, ItemTypeIds::WATER_BUCKET => $this->mix($item, VanillaItems::BUCKET(), $returnedItems),
			//TODO: tipped arrows
			default => null
		};
		return true;
	}

	public function onNearbyBlockChange() : void
	{
		$world = $this->position->getWorld();
		if ($world->getBlock($this->position->up())->getTypeId() === BlockTypeIds::WATER) {
			$cauldron = VanillaBlocks::WATER_CAULDRON()->setFillLevel(FillableCauldron::MAX_FILL_LEVEL);
			$world->setBlock($this->position, $cauldron);
			$world->addSound($this->position->add(0.5, 0.5, 0.5), $cauldron->getFillSound());
		}
	}
}
