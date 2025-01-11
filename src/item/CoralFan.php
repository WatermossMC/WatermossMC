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

namespace watermossmc\item;

use watermossmc\block\Block;
use watermossmc\block\utils\CoralTypeTrait;
use watermossmc\block\VanillaBlocks;
use watermossmc\data\runtime\RuntimeDataDescriber;
use watermossmc\math\Axis;
use watermossmc\math\Facing;

final class CoralFan extends Item
{
	use CoralTypeTrait {
		describeBlockItemState as encodeCoralType;
	}

	public function __construct(ItemIdentifier $identifier)
	{
		parent::__construct($identifier, VanillaBlocks::CORAL_FAN()->getName());
	}

	protected function describeState(RuntimeDataDescriber $w) : void
	{
		//this is aliased to ensure a compile error in case the functions in Item or Block start to differ in future
		//right now we can directly reuse encodeType from CoralTypeTrait, but that might silently stop working if Item
		//were to be altered. CoralTypeTrait was originally intended for blocks, so it's better not to assume anything.
		$this->encodeCoralType($w);
	}

	public function getBlock(?int $clickedFace = null) : Block
	{
		$block = $clickedFace !== null && Facing::axis($clickedFace) !== Axis::Y ? VanillaBlocks::WALL_CORAL_FAN() : VanillaBlocks::CORAL_FAN();

		return $block->setCoralType($this->coralType)->setDead($this->dead);
	}

	public function getFuelTime() : int
	{
		return $this->getBlock()->getFuelTime();
	}

	public function getMaxStackSize() : int
	{
		return $this->getBlock()->getMaxStackSize();
	}
}
