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

namespace watermossmc\item\enchantment;

/**
 * Container for enchantment data applied to items.
 *
 * Note: This class is assumed to be immutable. Consider this before making alterations.
 */
final class EnchantmentInstance
{
	public function __construct(
		private Enchantment $enchantment,
		private int $level = 1
	) {
	}

	/**
	 * Returns the type of this enchantment.
	 */
	public function getType() : Enchantment
	{
		return $this->enchantment;
	}

	/**
	 * Returns the level of the enchantment.
	 */
	public function getLevel() : int
	{
		return $this->level;
	}
}
