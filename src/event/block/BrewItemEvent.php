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

namespace watermossmc\event\block;

use watermossmc\block\tile\BrewingStand;
use watermossmc\crafting\BrewingRecipe;
use watermossmc\event\Cancellable;
use watermossmc\event\CancellableTrait;
use watermossmc\item\Item;

class BrewItemEvent extends BlockEvent implements Cancellable
{
	use CancellableTrait;

	public function __construct(
		private BrewingStand $brewingStand,
		private int $slot,
		private Item $input,
		private Item $result,
		private BrewingRecipe $recipe
	) {
		parent::__construct($brewingStand->getBlock());
	}

	public function getBrewingStand() : BrewingStand
	{
		return $this->brewingStand;
	}

	/**
	 * Returns which slot of the brewing stand's inventory the potion is in.
	 */
	public function getSlot() : int
	{
		return $this->slot;
	}

	public function getInput() : Item
	{
		return clone $this->input;
	}

	public function getResult() : Item
	{
		return clone $this->result;
	}

	public function setResult(Item $result) : void
	{
		$this->result = clone $result;
	}

	public function getRecipe() : BrewingRecipe
	{
		return $this->recipe;
	}
}
