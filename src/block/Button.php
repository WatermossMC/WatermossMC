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

use watermossmc\block\utils\AnyFacingTrait;
use watermossmc\data\runtime\RuntimeDataDescriber;
use watermossmc\item\Item;
use watermossmc\math\Facing;
use watermossmc\math\Vector3;
use watermossmc\player\Player;
use watermossmc\world\BlockTransaction;
use watermossmc\world\sound\RedstonePowerOffSound;
use watermossmc\world\sound\RedstonePowerOnSound;

abstract class Button extends Flowable
{
	use AnyFacingTrait;

	protected bool $pressed = false;

	protected function describeBlockOnlyState(RuntimeDataDescriber $w) : void
	{
		$w->facing($this->facing);
		$w->bool($this->pressed);
	}

	public function isPressed() : bool
	{
		return $this->pressed;
	}

	/** @return $this */
	public function setPressed(bool $pressed) : self
	{
		$this->pressed = $pressed;
		return $this;
	}

	public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool
	{
		if ($this->canBeSupportedAt($blockReplace, $face)) {
			$this->facing = $face;
			return parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player);
		}
		return false;
	}

	abstract protected function getActivationTime() : int;

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null, array &$returnedItems = []) : bool
	{
		if (!$this->pressed) {
			$this->pressed = true;
			$world = $this->position->getWorld();
			$world->setBlock($this->position, $this);
			$world->scheduleDelayedBlockUpdate($this->position, $this->getActivationTime());
			$world->addSound($this->position->add(0.5, 0.5, 0.5), new RedstonePowerOnSound());
		}

		return true;
	}

	public function onScheduledUpdate() : void
	{
		if ($this->pressed) {
			$this->pressed = false;
			$world = $this->position->getWorld();
			$world->setBlock($this->position, $this);
			$world->addSound($this->position->add(0.5, 0.5, 0.5), new RedstonePowerOffSound());
		}
	}

	public function onNearbyBlockChange() : void
	{
		if (!$this->canBeSupportedAt($this, $this->facing)) {
			$this->position->getWorld()->useBreakOn($this->position);
		}
	}

	private function canBeSupportedAt(Block $block, int $face) : bool
	{
		return $block->getAdjacentSupportType(Facing::opposite($face))->hasCenterSupport();
	}
}
