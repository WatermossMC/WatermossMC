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

use watermossmc\utils\LegacyEnumShimTrait;

/**
 * TODO: These tags need to be removed once we get rid of LegacyEnumShimTrait (PM6)
 *  These are retained for backwards compatibility only.
 *
 * @method static MushroomBlockType ALL_CAP()
 * @method static MushroomBlockType CAP_EAST()
 * @method static MushroomBlockType CAP_MIDDLE()
 * @method static MushroomBlockType CAP_NORTH()
 * @method static MushroomBlockType CAP_NORTHEAST()
 * @method static MushroomBlockType CAP_NORTHWEST()
 * @method static MushroomBlockType CAP_SOUTH()
 * @method static MushroomBlockType CAP_SOUTHEAST()
 * @method static MushroomBlockType CAP_SOUTHWEST()
 * @method static MushroomBlockType CAP_WEST()
 * @method static MushroomBlockType PORES()
 */
enum MushroomBlockType
{
	use LegacyEnumShimTrait;

	case PORES;
	case CAP_NORTHWEST;
	case CAP_NORTH;
	case CAP_NORTHEAST;
	case CAP_WEST;
	case CAP_MIDDLE;
	case CAP_EAST;
	case CAP_SOUTHWEST;
	case CAP_SOUTH;
	case CAP_SOUTHEAST;
	case ALL_CAP;
}
