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

use function count;

final class RecipeUnlockingRequirement
{
	/**
	 * @param RecipeIngredient[]|null $unlockingIngredients
	 * @phpstan-param list<RecipeIngredient>|null $unlockingIngredients
	 */
	public function __construct(
		private ?array $unlockingIngredients
	) {
	}

	/**
	 * @return RecipeIngredient[]|null
	 * @phpstan-return list<RecipeIngredient>|null
	 */
	public function getUnlockingIngredients() : ?array
	{
		return $this->unlockingIngredients;
	}

	public static function read(PacketSerializer $in) : self
	{
		//I don't know what the point of this structure is. It could easily have been a list<RecipeIngredient> instead.
		//It's basically just an optional list, which could have been done by an empty list wherever it's not needed.
		$unlockingContext = $in->getBool();
		$unlockingIngredients = null;
		if (!$unlockingContext) {
			$unlockingIngredients = [];
			for ($i = 0, $count = $in->getUnsignedVarInt(); $i < $count; $i++) {
				$unlockingIngredients[] = $in->getRecipeIngredient();
			}
		}

		return new self($unlockingIngredients);
	}

	public function write(PacketSerializer $out) : void
	{
		$out->putBool($this->unlockingIngredients === null);
		if ($this->unlockingIngredients !== null) {
			$out->putUnsignedVarInt(count($this->unlockingIngredients));
			foreach ($this->unlockingIngredients as $ingredient) {
				$out->putRecipeIngredient($ingredient);
			}
		}
	}
}
