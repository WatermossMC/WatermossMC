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

use watermossmc\inventory\SimpleInventory;
use watermossmc\network\mcpe\protocol\BlockEventPacket;
use watermossmc\network\mcpe\protocol\types\BlockPosition;
use watermossmc\world\Position;
use watermossmc\world\sound\ChestCloseSound;
use watermossmc\world\sound\ChestOpenSound;
use watermossmc\world\sound\Sound;

class ChestInventory extends SimpleInventory implements BlockInventory
{
	use AnimatedBlockInventoryTrait;

	public function __construct(Position $holder)
	{
		$this->holder = $holder;
		parent::__construct(27);
	}

	protected function getOpenSound() : Sound
	{
		return new ChestOpenSound();
	}

	protected function getCloseSound() : Sound
	{
		return new ChestCloseSound();
	}

	public function animateBlock(bool $isOpen) : void
	{
		$holder = $this->getHolder();

		//event ID is always 1 for a chest
		$holder->getWorld()->broadcastPacketToViewers($holder, BlockEventPacket::create(BlockPosition::fromVector3($holder), 1, $isOpen ? 1 : 0));
	}
}
