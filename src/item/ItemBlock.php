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
use watermossmc\block\BlockTypeIds;
use watermossmc\data\runtime\RuntimeDataDescriber;

/**
 * Class used for Items that directly represent blocks, such as stone, dirt, wood etc.
 *
 * This should NOT be used for items which are merely *associated* with blocks (e.g. seeds are not wheat crops; they
 * just place wheat crops when used on the ground).
 */
final class ItemBlock extends Item
{
	public function __construct(
		private Block $block
	) {
		parent::__construct(ItemIdentifier::fromBlock($block), $block->getName(), $block->getEnchantmentTags());
	}

	protected function describeState(RuntimeDataDescriber $w) : void
	{
		$this->block->describeBlockItemState($w);
	}

	public function getBlock(?int $clickedFace = null) : Block
	{
		return clone $this->block;
	}

	public function getFuelTime() : int
	{
		return $this->block->getFuelTime();
	}

	public function isFireProof() : bool
	{
		return $this->block->isFireProofAsItem();
	}

	public function getMaxStackSize() : int
	{
		return $this->block->getMaxStackSize();
	}

	public function isNull() : bool
	{
		//TODO: we really shouldn't need to treat air as a special case here
		//this is needed because the "null" empty slot item is represented by an air block, but there's no real reason
		//why air should be needed at all. A separate special item type (or actual null) should be used instead, but
		//this would cause a lot of BC breaks, so we can't do it yet.
		return parent::isNull() || $this->block->getTypeId() === BlockTypeIds::AIR;
	}
}
