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

use watermossmc\data\runtime\RuntimeDataDescriber;
use watermossmc\item\Item;
use watermossmc\math\Vector3;
use watermossmc\player\Player;

final class Light extends Flowable
{
	public const MIN_LIGHT_LEVEL = 0;
	public const MAX_LIGHT_LEVEL = 15;

	private int $level = self::MAX_LIGHT_LEVEL;

	public function describeBlockItemState(RuntimeDataDescriber $w) : void
	{
		$w->boundedIntAuto(self::MIN_LIGHT_LEVEL, self::MAX_LIGHT_LEVEL, $this->level);
	}

	public function getLightLevel() : int
	{
		return $this->level;
	}

	/** @return $this */
	public function setLightLevel(int $level) : self
	{
		if ($level < self::MIN_LIGHT_LEVEL || $level > self::MAX_LIGHT_LEVEL) {
			throw new \InvalidArgumentException("Light level must be in the range " . self::MIN_LIGHT_LEVEL . " ... " . self::MAX_LIGHT_LEVEL);
		}
		$this->level = $level;
		return $this;
	}

	public function canBeReplaced() : bool
	{
		return true;
	}

	public function canBePlacedAt(Block $blockReplace, Vector3 $clickVector, int $face, bool $isClickedBlock) : bool
	{
		//light blocks behave like solid blocks when placing them on another light block
		return $blockReplace->canBeReplaced() && $blockReplace->getTypeId() !== $this->getTypeId();
	}

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null, array &$returnedItems = []) : bool
	{
		$this->level = $this->level === self::MAX_LIGHT_LEVEL ?
			self::MIN_LIGHT_LEVEL :
			$this->level + 1;

		$this->position->getWorld()->setBlock($this->position, $this);

		return true;
	}
}
