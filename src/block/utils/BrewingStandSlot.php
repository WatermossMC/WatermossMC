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

namespace watermossmc\block\utils;

use watermossmc\block\inventory\BrewingStandInventory;
use watermossmc\utils\LegacyEnumShimTrait;

/**
 * TODO: These tags need to be removed once we get rid of LegacyEnumShimTrait (PM6)
 *  These are retained for backwards compatibility only.
 *
 * @method static BrewingStandSlot EAST()
 * @method static BrewingStandSlot NORTHWEST()
 * @method static BrewingStandSlot SOUTHWEST()
 */
enum BrewingStandSlot
{
	use LegacyEnumShimTrait;

	case EAST;
	case NORTHWEST;
	case SOUTHWEST;

	/**
	 * Returns the brewing stand inventory slot number associated with this visual slot.
	 */
	public function getSlotNumber() : int
	{
		return match($this) {
			self::EAST => BrewingStandInventory::SLOT_BOTTLE_LEFT,
			self::NORTHWEST => BrewingStandInventory::SLOT_BOTTLE_MIDDLE,
			self::SOUTHWEST => BrewingStandInventory::SLOT_BOTTLE_RIGHT
		};
	}
}
