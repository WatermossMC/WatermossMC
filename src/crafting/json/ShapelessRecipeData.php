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

namespace watermossmc\crafting\json;

use function count;

final class ShapelessRecipeData implements \JsonSerializable
{
	/**
	 * @required
	 * @var RecipeIngredientData[]
	 * @phpstan-var list<RecipeIngredientData>
	 */
	public array $input;

	/**
	 * @required
	 * @var ItemStackData[]
	 * @phpstan-var list<ItemStackData>
	 */
	public array $output;

	/** @required */
	public string $block;

	/** @required */
	public int $priority;

	/** @var RecipeIngredientData[] */
	public array $unlockingIngredients = [];

	/**
	 * @param RecipeIngredientData[] $input
	 * @param ItemStackData[]        $output
	 * @param RecipeIngredientData[] $unlockingIngredients
	 *
	 * @phpstan-param list<RecipeIngredientData> $input
	 * @phpstan-param list<ItemStackData> $output
	 * @phpstan-param list<RecipeIngredientData> $unlockingIngredients
	 */
	public function __construct(array $input, array $output, string $block, int $priority, array $unlockingIngredients = [])
	{
		$this->block = $block;
		$this->priority = $priority;
		$this->input = $input;
		$this->output = $output;
		$this->unlockingIngredients = $unlockingIngredients;
	}

	/**
	 * @return mixed[]
	 */
	public function jsonSerialize() : array
	{
		$result = (array) $this;
		if (count($this->unlockingIngredients) === 0) {
			unset($result["unlockingIngredients"]);
		}
		return $result;
	}
}
