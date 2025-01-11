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
use watermossmc\world\format\io\GlobalItemDataHandlers;

/**
 * Recipe ingredient that matches items by their Minecraft ID only. This is used for things like the crafting table
 * recipe from planks (multiple types of planks are accepted).
 *
 * WARNING: Plugins shouldn't usually use this. This is a hack that relies on internal Minecraft behaviour, which might
 * change or break at any time.
 *
 * @internal
 */
final class MetaWildcardRecipeIngredient implements RecipeIngredient
{
	public function __construct(
		private string $itemId,
	) {
	}

	public function getItemId() : string
	{
		return $this->itemId;
	}

	public function accepts(Item $item) : bool
	{
		if ($item->getCount() < 1) {
			return false;
		}

		return GlobalItemDataHandlers::getSerializer()->serializeType($item)->getName() === $this->itemId;
	}

	public function __toString() : string
	{
		return "MetaWildcardRecipeIngredient($this->itemId)";
	}
}
