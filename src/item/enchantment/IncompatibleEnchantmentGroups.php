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
 * Constants for groupings of incompatible enchantments.
 * Enchantments belonging to the same incompatibility group cannot be applied side-by-side on the same item.
 */
final class IncompatibleEnchantmentGroups
{
	public const PROTECTION = "protection";
	public const BOW_INFINITE = "bow_infinite";
	public const BLOCK_DROPS = "block_drops";
}
