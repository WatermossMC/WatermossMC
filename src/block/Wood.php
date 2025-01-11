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

use watermossmc\block\utils\PillarRotationTrait;
use watermossmc\block\utils\WoodTypeTrait;
use watermossmc\data\runtime\RuntimeDataDescriber;
use watermossmc\item\Axe;
use watermossmc\item\Item;
use watermossmc\math\Vector3;
use watermossmc\player\Player;
use watermossmc\world\sound\ItemUseOnBlockSound;

class Wood extends Opaque
{
	use PillarRotationTrait;
	use WoodTypeTrait;

	private bool $stripped = false;

	public function describeBlockItemState(RuntimeDataDescriber $w) : void
	{
		$w->bool($this->stripped);
	}

	public function isStripped() : bool
	{
		return $this->stripped;
	}

	/** @return $this */
	public function setStripped(bool $stripped) : self
	{
		$this->stripped = $stripped;
		return $this;
	}

	public function getFuelTime() : int
	{
		return $this->woodType->isFlammable() ? 300 : 0;
	}

	public function getFlameEncouragement() : int
	{
		return $this->woodType->isFlammable() ? 5 : 0;
	}

	public function getFlammability() : int
	{
		return $this->woodType->isFlammable() ? 5 : 0;
	}

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null, array &$returnedItems = []) : bool
	{
		if (!$this->stripped && $item instanceof Axe) {
			$item->applyDamage(1);
			$this->stripped = true;
			$this->position->getWorld()->setBlock($this->position, $this);
			$this->position->getWorld()->addSound($this->position, new ItemUseOnBlockSound($this));
			return true;
		}
		return false;
	}
}
