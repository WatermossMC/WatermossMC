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
