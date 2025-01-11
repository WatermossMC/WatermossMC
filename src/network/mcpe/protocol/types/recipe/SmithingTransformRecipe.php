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

use watermossmc\network\mcpe\protocol\serializer\PacketSerializer;
use watermossmc\network\mcpe\protocol\types\inventory\ItemStack;

final class SmithingTransformRecipe extends RecipeWithTypeId
{
	public function __construct(
		int $typeId,
		private string $recipeId,
		private RecipeIngredient $template,
		private RecipeIngredient $input,
		private RecipeIngredient $addition,
		private ItemStack $output,
		private string $blockName,
		private int $recipeNetId
	) {
		parent::__construct($typeId);
	}

	public function getRecipeId() : string
	{
		return $this->recipeId;
	}

	public function getTemplate() : RecipeIngredient
	{
		return $this->template;
	}

	public function getInput() : RecipeIngredient
	{
		return $this->input;
	}

	public function getAddition() : RecipeIngredient
	{
		return $this->addition;
	}

	public function getOutput() : ItemStack
	{
		return $this->output;
	}

	public function getBlockName() : string
	{
		return $this->blockName;
	}

	public function getRecipeNetId() : int
	{
		return $this->recipeNetId;
	}

	public static function decode(int $typeId, PacketSerializer $in) : self
	{
		$recipeId = $in->getString();
		$template = $in->getRecipeIngredient();
		$input = $in->getRecipeIngredient();
		$addition = $in->getRecipeIngredient();
		$output = $in->getItemStackWithoutStackId();
		$blockName = $in->getString();
		$recipeNetId = $in->readRecipeNetId();

		return new self(
			$typeId,
			$recipeId,
			$template,
			$input,
			$addition,
			$output,
			$blockName,
			$recipeNetId
		);
	}

	public function encode(PacketSerializer $out) : void
	{
		$out->putString($this->recipeId);
		$out->putRecipeIngredient($this->template);
		$out->putRecipeIngredient($this->input);
		$out->putRecipeIngredient($this->addition);
		$out->putItemStackWithoutStackId($this->output);
		$out->putString($this->blockName);
		$out->writeRecipeNetId($this->recipeNetId);
	}
}
