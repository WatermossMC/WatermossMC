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

use watermossmc\block\utils\CandleTrait;
use watermossmc\entity\Living;
use watermossmc\item\Item;
use watermossmc\math\AxisAlignedBB;
use watermossmc\math\Facing;
use watermossmc\math\Vector3;
use watermossmc\player\Player;

class CakeWithCandle extends BaseCake
{
	use CandleTrait {
		onInteract as onInteractCandle;
	}

	/**
	 * @return AxisAlignedBB[]
	 */
	protected function recalculateCollisionBoxes() : array
	{
		return [
			AxisAlignedBB::one()
				->contract(1 / 16, 0, 1 / 16)
				->trim(Facing::UP, 0.5) //TODO: not sure if the candle affects height
		];
	}

	public function getCandle() : Candle
	{
		return VanillaBlocks::CANDLE();
	}

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null, array &$returnedItems = []) : bool
	{
		if ($this->lit && $face !== Facing::UP) {
			return true;
		}
		if ($this->onInteractCandle($item, $face, $clickVector, $player, $returnedItems)) {
			return true;
		}

		return parent::onInteract($item, $face, $clickVector, $player, $returnedItems);
	}

	public function getDropsForCompatibleTool(Item $item) : array
	{
		return [$this->getCandle()->asItem()];
	}

	public function getPickedItem(bool $addUserData = false) : Item
	{
		return VanillaBlocks::CAKE()->asItem();
	}

	public function getResidue() : Block
	{
		return VanillaBlocks::CAKE()->setBites(1);
	}

	public function onConsume(Living $consumer) : void
	{
		parent::onConsume($consumer);
		$this->position->getWorld()->dropItem($this->position->add(0.5, 0.5, 0.5), $this->getCandle()->asItem());
	}
}
