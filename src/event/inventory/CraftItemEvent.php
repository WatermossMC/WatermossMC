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

namespace watermossmc\event\inventory;

use watermossmc\crafting\CraftingRecipe;
use watermossmc\event\Cancellable;
use watermossmc\event\CancellableTrait;
use watermossmc\event\Event;
use watermossmc\inventory\transaction\CraftingTransaction;
use watermossmc\item\Item;
use watermossmc\player\Player;
use watermossmc\utils\Utils;

class CraftItemEvent extends Event implements Cancellable
{
	use CancellableTrait;

	/**
	 * @param Item[] $inputs
	 * @param Item[] $outputs
	 */
	public function __construct(
		private CraftingTransaction $transaction,
		private CraftingRecipe $recipe,
		private int $repetitions,
		private array $inputs,
		private array $outputs
	) {
	}

	/**
	 * Returns the inventory transaction involved in this crafting event.
	 */
	public function getTransaction() : CraftingTransaction
	{
		return $this->transaction;
	}

	/**
	 * Returns the recipe crafted.
	 */
	public function getRecipe() : CraftingRecipe
	{
		return $this->recipe;
	}

	/**
	 * Returns the number of times the recipe was crafted. This is usually 1, but might be more in the case of recipe
	 * book shift-clicks (which craft lots of items in a batch).
	 */
	public function getRepetitions() : int
	{
		return $this->repetitions;
	}

	/**
	 * Returns a list of items destroyed as ingredients of the recipe.
	 *
	 * @return Item[]
	 */
	public function getInputs() : array
	{
		return Utils::cloneObjectArray($this->inputs);
	}

	/**
	 * Returns a list of items created by crafting the recipe.
	 *
	 * @return Item[]
	 */
	public function getOutputs() : array
	{
		return Utils::cloneObjectArray($this->outputs);
	}

	public function getPlayer() : Player
	{
		return $this->transaction->getSource();
	}
}
