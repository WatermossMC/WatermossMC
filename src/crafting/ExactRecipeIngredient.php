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

/**
 * Recipe ingredient that matches exactly one item, without wildcards.
 * Note that recipe inputs cannot require NBT.
 */
final class ExactRecipeIngredient implements RecipeIngredient
{
	public function __construct(private Item $item)
	{
		if ($item->isNull()) {
			throw new \InvalidArgumentException("Recipe ingredients must not be air items");
		}
		if ($item->getCount() !== 1) {
			throw new \InvalidArgumentException("Recipe ingredients cannot require count");
		}
		$this->item = clone $item;
	}

	public function getItem() : Item
	{
		return clone $this->item;
	}

	public function accepts(Item $item) : bool
	{
		//client-side, recipe inputs can't actually require NBT
		//but on the PM side, we currently check for it if the input requires it, so we have to continue to do so for
		//the sake of consistency
		return $item->getCount() >= 1 && $this->item->equals($item, true, $this->item->hasNamedTag());
	}

	public function __toString() : string
	{
		return "ExactRecipeIngredient(" . $this->item . ")";
	}
}
