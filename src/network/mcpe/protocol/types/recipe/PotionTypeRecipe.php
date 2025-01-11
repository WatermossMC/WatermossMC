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

namespace watermossmc\network\mcpe\protocol\types\recipe;

class PotionTypeRecipe
{
	public function __construct(
		private int $inputItemId,
		private int $inputItemMeta,
		private int $ingredientItemId,
		private int $ingredientItemMeta,
		private int $outputItemId,
		private int $outputItemMeta
	) {
	}

	public function getInputItemId() : int
	{
		return $this->inputItemId;
	}

	public function getInputItemMeta() : int
	{
		return $this->inputItemMeta;
	}

	public function getIngredientItemId() : int
	{
		return $this->ingredientItemId;
	}

	public function getIngredientItemMeta() : int
	{
		return $this->ingredientItemMeta;
	}

	public function getOutputItemId() : int
	{
		return $this->outputItemId;
	}

	public function getOutputItemMeta() : int
	{
		return $this->outputItemMeta;
	}
}
