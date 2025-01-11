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

namespace watermossmc\crafting;

use watermossmc\item\Item;

class PotionTypeRecipe implements BrewingRecipe
{
	public function __construct(
		private RecipeIngredient $input,
		private RecipeIngredient $ingredient,
		private Item $output
	) {
		$this->output = clone $output;
	}

	public function getInput() : RecipeIngredient
	{
		return $this->input;
	}

	public function getIngredient() : RecipeIngredient
	{
		return $this->ingredient;
	}

	public function getOutput() : Item
	{
		return clone $this->output;
	}

	public function getResultFor(Item $input) : ?Item
	{
		return $this->input->accepts($input) ? $this->getOutput() : null;
	}
}
