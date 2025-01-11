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

use watermossmc\data\bedrock\ItemTagToIdMap;
use watermossmc\item\Item;
use watermossmc\world\format\io\GlobalItemDataHandlers;

/**
 * Recipe ingredient that matches items whose ID falls within a specific set. This is used for magic meta value
 * wildcards and also for ingredients which use item tags (since tags implicitly rely on ID only).
 *
 * @internal
 */
final class TagWildcardRecipeIngredient implements RecipeIngredient
{
	public function __construct(
		private string $tagName
	) {
	}

	public function getTagName() : string
	{
		return $this->tagName;
	}

	public function accepts(Item $item) : bool
	{
		if ($item->getCount() < 1) {
			return false;
		}

		return ItemTagToIdMap::getInstance()->tagContainsId($this->tagName, GlobalItemDataHandlers::getSerializer()->serializeType($item)->getName());
	}

	public function __toString() : string
	{
		return "TagWildcardRecipeIngredient($this->tagName)";
	}
}
