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

/**
 * Repair and/or remove enchantments from an item in a grindstone.
 */
final class GrindstoneStackRequestAction extends ItemStackRequestAction
{
	use GetTypeIdFromConstTrait;

	public const ID = ItemStackRequestActionType::CRAFTING_GRINDSTONE;

	public function __construct(
		private int $recipeId,
		private int $repairCost,
		private int $repetitions
	) {
	}

	public function getRecipeId() : int
	{
		return $this->recipeId;
	}

	/** WARNING: This may be negative */
	public function getRepairCost() : int
	{
		return $this->repairCost;
	}

	public function getRepetitions() : int
	{
		return $this->repetitions;
	}

	public static function read(PacketSerializer $in) : self
	{
		$recipeId = $in->readRecipeNetId();
		$repairCost = $in->getVarInt(); //WHY!!!!
		$repetitions = $in->getByte();

		return new self($recipeId, $repairCost, $repetitions);
	}

	public function write(PacketSerializer $out) : void
	{
		$out->writeRecipeNetId($this->recipeId);
		$out->putVarInt($this->repairCost);
		$out->putByte($this->repetitions);
	}
}
