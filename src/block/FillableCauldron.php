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

use watermossmc\block\utils\SupportType;
use watermossmc\data\runtime\RuntimeDataDescriber;
use watermossmc\item\Item;
use watermossmc\math\AxisAlignedBB;
use watermossmc\math\Facing;
use watermossmc\world\sound\Sound;

use function min;

abstract class FillableCauldron extends Transparent
{
	public const MIN_FILL_LEVEL = 1;
	public const MAX_FILL_LEVEL = 6;

	private int $fillLevel = self::MIN_FILL_LEVEL;

	protected function describeBlockOnlyState(RuntimeDataDescriber $w) : void
	{
		$w->boundedIntAuto(self::MIN_FILL_LEVEL, self::MAX_FILL_LEVEL, $this->fillLevel);
	}

	public function getFillLevel() : int
	{
		return $this->fillLevel;
	}

	/** @return $this */
	public function setFillLevel(int $fillLevel) : self
	{
		if ($fillLevel < self::MIN_FILL_LEVEL || $fillLevel > self::MAX_FILL_LEVEL) {
			throw new \InvalidArgumentException("Fill level must be in range " . self::MIN_FILL_LEVEL . " ... " . self::MAX_FILL_LEVEL);
		}
		$this->fillLevel = $fillLevel;
		return $this;
	}

	protected function recalculateCollisionBoxes() : array
	{
		$result = [
			AxisAlignedBB::one()->trim(Facing::UP, 11 / 16) //bottom of the cauldron
		];

		foreach (Facing::HORIZONTAL as $f) { //add the frame parts around the bowl
			$result[] = AxisAlignedBB::one()->trim($f, 14 / 16);
		}
		return $result;
	}

	public function getSupportType(int $facing) : SupportType
	{
		return $facing === Facing::UP ? SupportType::EDGE : SupportType::NONE;
	}

	protected function withFillLevel(int $fillLevel) : Block
	{
		return $fillLevel === 0 ? VanillaBlocks::CAULDRON() : $this->setFillLevel(min(self::MAX_FILL_LEVEL, $fillLevel));
	}

	/**
	 * @param Item[] &$returnedItems
	 */
	protected function addFillLevels(int $amount, Item $usedItem, Item $returnedItem, array &$returnedItems) : void
	{
		if ($this->fillLevel >= self::MAX_FILL_LEVEL) {
			return;
		}
		$this->position->getWorld()->setBlock($this->position, $this->withFillLevel($this->fillLevel + $amount));
		$this->position->getWorld()->addSound($this->position->add(0.5, 0.5, 0.5), $this->getFillSound());

		$usedItem->pop();
		$returnedItems[] = $returnedItem;
	}

	/**
	 * @param Item[] &$returnedItems
	 */
	protected function removeFillLevels(int $amount, Item $usedItem, Item $returnedItem, array &$returnedItems) : void
	{
		if ($this->fillLevel < $amount) {
			return;
		}

		$this->position->getWorld()->setBlock($this->position, $this->withFillLevel($this->fillLevel - $amount));
		$this->position->getWorld()->addSound($this->position->add(0.5, 0.5, 0.5), $this->getEmptySound());

		$usedItem->pop();
		$returnedItems[] = $returnedItem;
	}

	/**
	 * Returns the sound played when adding levels to the cauldron liquid.
	 */
	abstract public function getFillSound() : Sound;

	/**
	 * Returns the sound played when removing levels from the cauldron liquid.
	 */
	abstract public function getEmptySound() : Sound;

	/**
	 * @param Item[] &$returnedItems
	 */
	protected function mix(Item $usedItem, Item $returnedItem, array &$returnedItems) : void
	{
		$this->position->getWorld()->setBlock($this->position, VanillaBlocks::CAULDRON());
		//TODO: sounds and particles

		$usedItem->pop();
		$returnedItems[] = $returnedItem;
	}

	public function asItem() : Item
	{
		return VanillaBlocks::CAULDRON()->asItem();
	}
}
