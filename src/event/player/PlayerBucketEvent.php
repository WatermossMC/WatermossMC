<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.watermossmc.net/
 *
 *
 */

declare(strict_types=1);

/*
 * This file is part of the WatermossMC
 * (c) 2025 WatermossMC <gameplaytebakgambard>
 *
 * @License Apache 2.0
 */

namespace watermossmc\event\player;

use watermossmc\block\Block;
use watermossmc\event\Cancellable;
use watermossmc\event\CancellableTrait;
use watermossmc\item\Item;
use watermossmc\player\Player;

/**
 * @allowHandle
 */
abstract class PlayerBucketEvent extends PlayerEvent implements Cancellable
{
	use CancellableTrait;

	public function __construct(
		Player $who,
		private Block $blockClicked,
		private int $blockFace,
		private Item $bucket,
		private Item $itemInHand
	) {
		$this->player = $who;
	}

	/**
	 * Returns the bucket used in this event
	 */
	public function getBucket() : Item
	{
		return $this->bucket;
	}

	/**
	 * Returns the item in hand after the event
	 */
	public function getItem() : Item
	{
		return $this->itemInHand;
	}

	public function setItem(Item $item) : void
	{
		$this->itemInHand = $item;
	}

	public function getBlockClicked() : Block
	{
		return $this->blockClicked;
	}

	public function getBlockFace() : int
	{
		return $this->blockFace;
	}
}
