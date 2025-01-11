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

namespace watermossmc\item;

use watermossmc\block\Block;
use watermossmc\block\Lava;
use watermossmc\block\Liquid;
use watermossmc\event\player\PlayerBucketEmptyEvent;
use watermossmc\math\Vector3;
use watermossmc\player\Player;

class LiquidBucket extends Item
{
	private Liquid $liquid;

	public function __construct(ItemIdentifier $identifier, string $name, Liquid $liquid)
	{
		parent::__construct($identifier, $name);
		$this->liquid = $liquid;
	}

	public function getMaxStackSize() : int
	{
		return 1;
	}

	public function getFuelTime() : int
	{
		if ($this->liquid instanceof Lava) {
			return 20000;
		}

		return 0;
	}

	public function getFuelResidue() : Item
	{
		return VanillaItems::BUCKET();
	}

	public function onInteractBlock(Player $player, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, array &$returnedItems) : ItemUseResult
	{
		if (!$blockReplace->canBeReplaced()) {
			return ItemUseResult::NONE;
		}

		//TODO: move this to generic placement logic
		$resultBlock = clone $this->liquid;

		$ev = new PlayerBucketEmptyEvent($player, $blockReplace, $face, $this, VanillaItems::BUCKET());
		$ev->call();
		if (!$ev->isCancelled()) {
			$player->getWorld()->setBlock($blockReplace->getPosition(), $resultBlock->getFlowingForm());
			$player->getWorld()->addSound($blockReplace->getPosition()->add(0.5, 0.5, 0.5), $resultBlock->getBucketEmptySound());

			$this->pop();
			$returnedItems[] = $ev->getItem();
			return ItemUseResult::SUCCESS;
		}

		return ItemUseResult::FAIL;
	}

	public function getLiquid() : Liquid
	{
		return $this->liquid;
	}
}
