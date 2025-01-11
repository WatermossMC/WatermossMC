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

use watermossmc\data\runtime\RuntimeDataDescriber;
use watermossmc\item\Item;
use watermossmc\item\ItemBlock;
use watermossmc\math\AxisAlignedBB;
use watermossmc\math\Facing;
use watermossmc\math\Vector3;
use watermossmc\player\Player;

class Cake extends BaseCake
{
	public const MAX_BITES = 6;

	protected int $bites = 0;

	protected function describeBlockOnlyState(RuntimeDataDescriber $w) : void
	{
		$w->boundedIntAuto(0, self::MAX_BITES, $this->bites);
	}

	/**
	 * @return AxisAlignedBB[]
	 */
	protected function recalculateCollisionBoxes() : array
	{
		return [
			AxisAlignedBB::one()
				->contract(1 / 16, 0, 1 / 16)
				->trim(Facing::UP, 0.5)
				->trim(Facing::WEST, $this->bites / 8)
		];
	}

	public function getBites() : int
	{
		return $this->bites;
	}

	/** @return $this */
	public function setBites(int $bites) : self
	{
		if ($bites < 0 || $bites > self::MAX_BITES) {
			throw new \InvalidArgumentException("Bites must be in range 0 ... " . self::MAX_BITES);
		}
		$this->bites = $bites;
		return $this;
	}

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null, array &$returnedItems = []) : bool
	{
		if ($this->bites === 0 && $item instanceof ItemBlock) {
			$block = $item->getBlock();
			$resultBlock = null;
			if ($block->getTypeId() === BlockTypeIds::CANDLE) {
				$resultBlock = VanillaBlocks::CAKE_WITH_CANDLE();
			} elseif ($block instanceof DyedCandle) {
				$resultBlock = VanillaBlocks::CAKE_WITH_DYED_CANDLE()->setColor($block->getColor());
			}

			if ($resultBlock !== null) {
				$this->position->getWorld()->setBlock($this->position, $resultBlock);
				$item->pop();
				return true;
			}
		}

		return parent::onInteract($item, $face, $clickVector, $player, $returnedItems);
	}

	public function getDropsForCompatibleTool(Item $item) : array
	{
		return [];
	}

	public function getResidue() : Block
	{
		$clone = clone $this;
		$clone->bites++;
		if ($clone->bites > self::MAX_BITES) {
			$clone = VanillaBlocks::AIR();
		}
		return $clone;
	}
}
