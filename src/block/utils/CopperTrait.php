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

namespace watermossmc\block\utils;

use watermossmc\data\runtime\RuntimeDataDescriber;
use watermossmc\item\Axe;
use watermossmc\item\Item;
use watermossmc\item\ItemTypeIds;
use watermossmc\math\Vector3;
use watermossmc\player\Player;
use watermossmc\world\sound\CopperWaxApplySound;
use watermossmc\world\sound\CopperWaxRemoveSound;
use watermossmc\world\sound\ScrapeSound;

trait CopperTrait
{
	private CopperOxidation $oxidation = CopperOxidation::NONE;
	private bool $waxed = false;

	public function describeBlockItemState(RuntimeDataDescriber $w) : void
	{
		$w->enum($this->oxidation);
		$w->bool($this->waxed);
	}

	public function getOxidation() : CopperOxidation
	{
		return $this->oxidation;
	}

	/** @return $this */
	public function setOxidation(CopperOxidation $oxidation) : self
	{
		$this->oxidation = $oxidation;
		return $this;
	}

	public function isWaxed() : bool
	{
		return $this->waxed;
	}

	/** @return $this */
	public function setWaxed(bool $waxed) : self
	{
		$this->waxed = $waxed;
		return $this;
	}

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null, array &$returnedItems = []) : bool
	{
		if (!$this->waxed && $item->getTypeId() === ItemTypeIds::HONEYCOMB) {
			$this->waxed = true;
			$this->position->getWorld()->setBlock($this->position, $this);
			//TODO: orange particles are supposed to appear when applying wax
			$this->position->getWorld()->addSound($this->position, new CopperWaxApplySound());
			$item->pop();
			return true;
		}

		if ($item instanceof Axe) {
			if ($this->waxed) {
				$this->waxed = false;
				$this->position->getWorld()->setBlock($this->position, $this);
				//TODO: white particles are supposed to appear when removing wax
				$this->position->getWorld()->addSound($this->position, new CopperWaxRemoveSound());
				$item->applyDamage(1);
				return true;
			}

			$previousOxidation = $this->oxidation->getPrevious();
			if ($previousOxidation !== null) {
				$this->oxidation = $previousOxidation;
				$this->position->getWorld()->setBlock($this->position, $this);
				//TODO: turquoise particles are supposed to appear when removing oxidation
				$this->position->getWorld()->addSound($this->position, new ScrapeSound());
				$item->applyDamage(1);
				return true;
			}
		}

		return false;
	}
}
