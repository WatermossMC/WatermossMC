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

final class PotionContainerChangeRecipeData
{
	/** @required */
	public string $input_item_name;

	/** @required */
	public RecipeIngredientData $ingredient;

	/** @required */
	public string $output_item_name;

	public function __construct(string $input_item_name, RecipeIngredientData $ingredient, string $output_item_name)
	{
		$this->input_item_name = $input_item_name;
		$this->ingredient = $ingredient;
		$this->output_item_name = $output_item_name;
	}
}
