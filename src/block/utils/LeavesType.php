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
 * @method static LeavesType ACACIA()
 * @method static LeavesType AZALEA()
 * @method static LeavesType BIRCH()
 * @method static LeavesType CHERRY()
 * @method static LeavesType DARK_OAK()
 * @method static LeavesType FLOWERING_AZALEA()
 * @method static LeavesType JUNGLE()
 * @method static LeavesType MANGROVE()
 * @method static LeavesType OAK()
 * @method static LeavesType SPRUCE()
 */
enum LeavesType
{
	use LegacyEnumShimTrait;

	case OAK;
	case SPRUCE;
	case BIRCH;
	case JUNGLE;
	case ACACIA;
	case DARK_OAK;
	case MANGROVE;
	case AZALEA;
	case FLOWERING_AZALEA;
	case CHERRY;

	public function getDisplayName() : string
	{
		return match($this) {
			self::OAK => "Oak",
			self::SPRUCE => "Spruce",
			self::BIRCH => "Birch",
			self::JUNGLE => "Jungle",
			self::ACACIA => "Acacia",
			self::DARK_OAK => "Dark Oak",
			self::MANGROVE => "Mangrove",
			self::AZALEA => "Azalea",
			self::FLOWERING_AZALEA => "Flowering Azalea",
			self::CHERRY => "Cherry"
		};
	}
}
