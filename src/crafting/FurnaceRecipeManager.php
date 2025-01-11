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
use watermossmc\utils\ObjectSet;

final class FurnaceRecipeManager
{
	/** @var FurnaceRecipe[] */
	protected array $furnaceRecipes = [];

	/**
	 * @var FurnaceRecipe[]
	 * @phpstan-var array<int, FurnaceRecipe>
	 */
	private array $lookupCache = [];

	/** @phpstan-var ObjectSet<\Closure(FurnaceRecipe) : void> */
	private ObjectSet $recipeRegisteredCallbacks;

	public function __construct()
	{
		$this->recipeRegisteredCallbacks = new ObjectSet();
	}

	/**
	 * @phpstan-return ObjectSet<\Closure(FurnaceRecipe) : void>
	 */
	public function getRecipeRegisteredCallbacks() : ObjectSet
	{
		return $this->recipeRegisteredCallbacks;
	}

	/**
	 * @return FurnaceRecipe[]
	 */
	public function getAll() : array
	{
		return $this->furnaceRecipes;
	}

	public function register(FurnaceRecipe $recipe) : void
	{
		$this->furnaceRecipes[] = $recipe;
		foreach ($this->recipeRegisteredCallbacks as $callback) {
			$callback($recipe);
		}
	}

	public function match(Item $input) : ?FurnaceRecipe
	{
		$index = $input->getStateId();
		$simpleRecipe = $this->lookupCache[$index] ?? null;
		if ($simpleRecipe !== null) {
			return $simpleRecipe;
		}

		foreach ($this->furnaceRecipes as $recipe) {
			if ($recipe->getInput()->accepts($input)) {
				//remember that this item is accepted by this recipe, so we don't need to bruteforce it again
				$this->lookupCache[$index] = $recipe;
				return $recipe;
			}
		}

		return null;
	}
}
