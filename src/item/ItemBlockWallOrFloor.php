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
use watermossmc\block\RuntimeBlockStateRegistry;
use watermossmc\math\Axis;
use watermossmc\math\Facing;

class ItemBlockWallOrFloor extends Item
{
	private int $floorVariant;
	private int $wallVariant;

	public function __construct(ItemIdentifier $identifier, Block $floorVariant, Block $wallVariant)
	{
		parent::__construct($identifier, $floorVariant->getName());
		$this->floorVariant = $floorVariant->getStateId();
		$this->wallVariant = $wallVariant->getStateId();
	}

	public function getBlock(?int $clickedFace = null) : Block
	{
		if ($clickedFace !== null && Facing::axis($clickedFace) !== Axis::Y) {
			return RuntimeBlockStateRegistry::getInstance()->fromStateId($this->wallVariant);
		}
		return RuntimeBlockStateRegistry::getInstance()->fromStateId($this->floorVariant);
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
