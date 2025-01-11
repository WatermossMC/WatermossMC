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
use watermossmc\network\mcpe\protocol\types\recipe\RecipeIngredient;

use function count;

/**
 * Tells that the current transaction crafted the specified recipe, using the recipe book. This is effectively the same
 * as the regular crafting result action.
 */
final class CraftRecipeAutoStackRequestAction extends ItemStackRequestAction
{
	use GetTypeIdFromConstTrait;

	public const ID = ItemStackRequestActionType::CRAFTING_RECIPE_AUTO;

	/**
	 * @param RecipeIngredient[] $ingredients
	 * @phpstan-param list<RecipeIngredient> $ingredients
	 */
	final public function __construct(
		private int $recipeId,
		private int $repetitions,
		private int $repetitions2,
		private array $ingredients
	) {
	}

	public function getRecipeId() : int
	{
		return $this->recipeId;
	}

	public function getRepetitions() : int
	{
		return $this->repetitions;
	}

	public function getRepetitions2() : int
	{
		return $this->repetitions2;
	}

	/**
	 * @return RecipeIngredient[]
	 * @phpstan-return list<RecipeIngredient>
	 */
	public function getIngredients() : array
	{
		return $this->ingredients;
	}

	public static function read(PacketSerializer $in) : self
	{
		$recipeId = $in->readRecipeNetId();
		$repetitions = $in->getByte();
		$repetitions2 = $in->getByte(); //repetitions property is sent twice, mojang...
		$ingredients = [];
		for ($i = 0, $count = $in->getByte(); $i < $count; ++$i) {
			$ingredients[] = $in->getRecipeIngredient();
		}
		return new self($recipeId, $repetitions, $repetitions2, $ingredients);
	}

	public function write(PacketSerializer $out) : void
	{
		$out->writeRecipeNetId($this->recipeId);
		$out->putByte($this->repetitions);
		$out->putByte($this->repetitions2);
		$out->putByte(count($this->ingredients));
		foreach ($this->ingredients as $ingredient) {
			$out->putRecipeIngredient($ingredient);
		}
	}
}
