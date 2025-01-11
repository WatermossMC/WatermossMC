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

use watermossmc\block\tile\EnderChest;
use watermossmc\inventory\DelegateInventory;
use watermossmc\inventory\Inventory;
use watermossmc\inventory\PlayerEnderInventory;
use watermossmc\network\mcpe\protocol\BlockEventPacket;
use watermossmc\network\mcpe\protocol\types\BlockPosition;
use watermossmc\player\Player;
use watermossmc\world\Position;
use watermossmc\world\sound\EnderChestCloseSound;
use watermossmc\world\sound\EnderChestOpenSound;
use watermossmc\world\sound\Sound;

/**
 * EnderChestInventory is not a real inventory; it's just a gateway to the player's ender inventory.
 */
class EnderChestInventory extends DelegateInventory implements BlockInventory
{
	use AnimatedBlockInventoryTrait {
		onClose as animatedBlockInventoryTrait_onClose;
	}

	public function __construct(
		Position $holder,
		private PlayerEnderInventory $inventory
	) {
		parent::__construct($inventory);
		$this->holder = $holder;
	}

	public function getEnderInventory() : PlayerEnderInventory
	{
		return $this->inventory;
	}

	public function getViewerCount() : int
	{
		$enderChest = $this->getHolder()->getWorld()->getTile($this->getHolder());
		if (!$enderChest instanceof EnderChest) {
			return 0;
		}
		return $enderChest->getViewerCount();
	}

	protected function getOpenSound() : Sound
	{
		return new EnderChestOpenSound();
	}

	protected function getCloseSound() : Sound
	{
		return new EnderChestCloseSound();
	}

	protected function animateBlock(bool $isOpen) : void
	{
		$holder = $this->getHolder();

		//event ID is always 1 for a chest
		$holder->getWorld()->broadcastPacketToViewers($holder, BlockEventPacket::create(BlockPosition::fromVector3($holder), 1, $isOpen ? 1 : 0));
	}

	public function onClose(Player $who) : void
	{
		$this->animatedBlockInventoryTrait_onClose($who);
		$enderChest = $this->getHolder()->getWorld()->getTile($this->getHolder());
		if ($enderChest instanceof EnderChest) {
			$enderChest->setViewerCount($enderChest->getViewerCount() - 1);
		}
	}
}
