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

namespace watermossmc\network\mcpe\protocol\types\inventory\stackrequest;

use watermossmc\network\mcpe\protocol\serializer\PacketSerializer;
use watermossmc\network\mcpe\protocol\types\GetTypeIdFromConstTrait;
use watermossmc\network\mcpe\protocol\types\inventory\ContainerUIIds;
use watermossmc\network\mcpe\protocol\types\inventory\UIInventorySlotOffset;

/**
 * This action precedes a "take" or "place" action involving the "created item" magic slot. It indicates that the
 * "created item" output slot now contains output N of a previously specified crafting recipe.
 * This is only used with crafting recipes that have multiple outputs. For recipes with single outputs, it's assumed
 * that the content of the "created item" slot is the only output.
 *
 * @see ContainerUIIds::CREATED_OUTPUT
 * @see UIInventorySlotOffset::CREATED_ITEM_OUTPUT
 */
final class CraftingCreateSpecificResultStackRequestAction extends ItemStackRequestAction
{
	use GetTypeIdFromConstTrait;

	public const ID = ItemStackRequestActionType::CRAFTING_CREATE_SPECIFIC_RESULT;

	public function __construct(
		private int $resultIndex
	) {
	}

	public function getResultIndex() : int
	{
		return $this->resultIndex;
	}

	public static function read(PacketSerializer $in) : self
	{
		$slot = $in->getByte();
		return new self($slot);
	}

	public function write(PacketSerializer $out) : void
	{
		$out->putByte($this->resultIndex);
	}
}
