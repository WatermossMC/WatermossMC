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

use watermossmc\data\bedrock\item\SavedItemData;
use watermossmc\item\Item;
use watermossmc\world\format\io\GlobalItemDataHandlers;

class PotionContainerChangeRecipe implements BrewingRecipe
{
	public function __construct(
		private string $inputItemId,
		private RecipeIngredient $ingredient,
		private string $outputItemId
	) {
	}

	public function getInputItemId() : string
	{
		return $this->inputItemId;
	}

	public function getIngredient() : RecipeIngredient
	{
		return $this->ingredient;
	}

	public function getOutputItemId() : string
	{
		return $this->outputItemId;
	}

	public function getResultFor(Item $input) : ?Item
	{
		//TODO: this is a really awful hack, but there isn't another way for now
		//this relies on transforming the serialized item's ID, relying on the target item type's data being the same as the input.
		//This is the same assumption previously made using ItemFactory::get(), except it was less obvious how bad it was.
		//The other way is to bake the actual Potion class types into here, which isn't great for data-driving stuff.
		//We need a better solution for this.

		$data = GlobalItemDataHandlers::getSerializer()->serializeType($input);
		return $data->getName() === $this->getInputItemId() ?
			GlobalItemDataHandlers::getDeserializer()->deserializeType(new SavedItemData($this->getOutputItemId(), $data->getMeta(), $data->getBlock(), $data->getTag())) :
			null;
	}
}
