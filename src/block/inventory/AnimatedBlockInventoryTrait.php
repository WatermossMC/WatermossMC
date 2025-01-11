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

use watermossmc\player\Player;
use watermossmc\world\sound\Sound;

use function count;

trait AnimatedBlockInventoryTrait
{
	use BlockInventoryTrait;

	public function getViewerCount() : int
	{
		return count($this->getViewers());
	}

	/**
	 * @return Player[]
	 * @phpstan-return array<int, Player>
	 */
	abstract public function getViewers() : array;

	abstract protected function getOpenSound() : Sound;

	abstract protected function getCloseSound() : Sound;

	public function onOpen(Player $who) : void
	{
		parent::onOpen($who);

		if ($this->holder->isValid() && $this->getViewerCount() === 1) {
			//TODO: this crap really shouldn't be managed by the inventory
			$this->animateBlock(true);
			$this->holder->getWorld()->addSound($this->holder->add(0.5, 0.5, 0.5), $this->getOpenSound());
		}
	}

	abstract protected function animateBlock(bool $isOpen) : void;

	public function onClose(Player $who) : void
	{
		if ($this->holder->isValid() && $this->getViewerCount() === 1) {
			//TODO: this crap really shouldn't be managed by the inventory
			$this->animateBlock(false);
			$this->holder->getWorld()->addSound($this->holder->add(0.5, 0.5, 0.5), $this->getCloseSound());
		}
		parent::onClose($who);
	}
}
