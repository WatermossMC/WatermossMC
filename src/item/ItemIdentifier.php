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

class ItemIdentifier
{
	public function __construct(
		private int $typeId
	) {
	}

	public static function fromBlock(Block $block) : self
	{
		//TODO: maybe an ItemBlockIdentifier is in order?
		return new self(ItemTypeIds::fromBlockTypeId($block->getTypeId()));
	}

	public function getTypeId() : int
	{
		return $this->typeId;
	}
}
