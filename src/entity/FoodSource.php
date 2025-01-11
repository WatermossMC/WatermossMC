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

namespace watermossmc\entity;

/**
 * Interface implemented by objects that can be consumed by players, giving them food and saturation.
 */
interface FoodSource extends Consumable
{
	public function getFoodRestore() : int;

	public function getSaturationRestore() : float;

	/**
	 * Returns whether a Human eating this FoodSource must have a non-full hunger bar.
	 * This is ignored in creative mode and in peaceful difficulty.
	 */
	public function requiresHunger() : bool;
}
