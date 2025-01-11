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

use watermossmc\block\Barrel;
use watermossmc\inventory\SimpleInventory;
use watermossmc\world\Position;
use watermossmc\world\sound\BarrelCloseSound;
use watermossmc\world\sound\BarrelOpenSound;
use watermossmc\world\sound\Sound;

class BarrelInventory extends SimpleInventory implements BlockInventory
{
	use AnimatedBlockInventoryTrait;

	public function __construct(Position $holder)
	{
		$this->holder = $holder;
		parent::__construct(27);
	}

	protected function getOpenSound() : Sound
	{
		return new BarrelOpenSound();
	}

	protected function getCloseSound() : Sound
	{
		return new BarrelCloseSound();
	}

	protected function animateBlock(bool $isOpen) : void
	{
		$holder = $this->getHolder();
		$world = $holder->getWorld();
		$block = $world->getBlock($holder);
		if ($block instanceof Barrel) {
			$world->setBlock($holder, $block->setOpen($isOpen));
		}
	}
}
