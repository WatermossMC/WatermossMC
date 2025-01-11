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

final class MaterialReducerRecipe
{
	/**
	 * @param MaterialReducerRecipeOutput[] $outputs
	 * @phpstan-param list<MaterialReducerRecipeOutput> $outputs
	 */
	public function __construct(
		private int $inputItemId,
		private int $inputItemMeta,
		private array $outputs
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

	/** @return MaterialReducerRecipeOutput[] */
	public function getOutputs() : array
	{
		return $this->outputs;
	}
}
