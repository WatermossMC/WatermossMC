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

/**
 * Represents a block which is unrecognized or not implemented.
 */
class UnknownBlock extends Transparent
{
	private int $stateData;

	public function __construct(BlockIdentifier $idInfo, BlockTypeInfo $typeInfo, int $stateData)
	{
		$this->stateData = $stateData;
		parent::__construct($idInfo, "Unknown", $typeInfo);
	}

	public function describeBlockItemState(RuntimeDataDescriber $w) : void
	{
		//use type instead of state, so we don't lose any information like colour
		//this might be an improperly registered plugin block
		$w->int(Block::INTERNAL_STATE_DATA_BITS, $this->stateData);
	}

	public function canBePlaced() : bool
	{
		return false;
	}

	public function getDrops(Item $item) : array
	{
		return [];
	}
}
