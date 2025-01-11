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

use watermossmc\block\utils\MushroomBlockType;
use watermossmc\data\runtime\RuntimeDataDescriber;
use watermossmc\item\Item;

use function mt_rand;

class RedMushroomBlock extends Opaque
{
	protected MushroomBlockType $mushroomBlockType = MushroomBlockType::ALL_CAP;

	public function describeBlockItemState(RuntimeDataDescriber $w) : void
	{
		//these blocks always drop as all-cap, but may exist in other forms in the inventory (particularly creative),
		//so this information needs to be kept in the type info
		$w->enum($this->mushroomBlockType);
	}

	public function getMushroomBlockType() : MushroomBlockType
	{
		return $this->mushroomBlockType;
	}

	/** @return $this */
	public function setMushroomBlockType(MushroomBlockType $mushroomBlockType) : self
	{
		$this->mushroomBlockType = $mushroomBlockType;
		return $this;
	}

	public function getDropsForCompatibleTool(Item $item) : array
	{
		return [
			VanillaBlocks::RED_MUSHROOM()->asItem()->setCount(mt_rand(0, 2))
		];
	}

	public function isAffectedBySilkTouch() : bool
	{
		return true;
	}

	public function getSilkTouchDrops(Item $item) : array
	{
		return [(clone $this)->setMushroomBlockType(MushroomBlockType::ALL_CAP)->asItem()];
	}

	public function getPickedItem(bool $addUserData = false) : Item
	{
		return (clone $this)->setMushroomBlockType(MushroomBlockType::ALL_CAP)->asItem();
	}
}
