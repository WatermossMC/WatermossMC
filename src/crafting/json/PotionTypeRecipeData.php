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

final class PotionTypeRecipeData
{
	/** @required */
	public RecipeIngredientData $input;

	/** @required */
	public RecipeIngredientData $ingredient;

	/** @required */
	public ItemStackData $output;

	public function __construct(RecipeIngredientData $input, RecipeIngredientData $ingredient, ItemStackData $output)
	{
		$this->input = $input;
		$this->ingredient = $ingredient;
		$this->output = $output;
	}
}
