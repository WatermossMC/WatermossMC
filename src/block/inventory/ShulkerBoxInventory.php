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

namespace watermossmc\block\inventory;

use watermossmc\block\BlockTypeIds;
use watermossmc\inventory\SimpleInventory;
use watermossmc\item\Item;
use watermossmc\item\ItemTypeIds;
use watermossmc\network\mcpe\protocol\BlockEventPacket;
use watermossmc\network\mcpe\protocol\types\BlockPosition;
use watermossmc\world\Position;
use watermossmc\world\sound\ShulkerBoxCloseSound;
use watermossmc\world\sound\ShulkerBoxOpenSound;
use watermossmc\world\sound\Sound;

class ShulkerBoxInventory extends SimpleInventory implements BlockInventory
{
	use AnimatedBlockInventoryTrait;

	public function __construct(Position $holder)
	{
		$this->holder = $holder;
		parent::__construct(27);
	}

	protected function getOpenSound() : Sound
	{
		return new ShulkerBoxOpenSound();
	}

	protected function getCloseSound() : Sound
	{
		return new ShulkerBoxCloseSound();
	}

	public function canAddItem(Item $item) : bool
	{
		$blockTypeId = ItemTypeIds::toBlockTypeId($item->getTypeId());
		if ($blockTypeId === BlockTypeIds::SHULKER_BOX || $blockTypeId === BlockTypeIds::DYED_SHULKER_BOX) {
			return false;
		}
		return parent::canAddItem($item);
	}

	protected function animateBlock(bool $isOpen) : void
	{
		$holder = $this->getHolder();

		//event ID is always 1 for a chest
		$holder->getWorld()->broadcastPacketToViewers($holder, BlockEventPacket::create(BlockPosition::fromVector3($holder), 1, $isOpen ? 1 : 0));
	}
}
