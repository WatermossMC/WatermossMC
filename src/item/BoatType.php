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

namespace watermossmc\item;

use watermossmc\block\utils\WoodType;
use watermossmc\utils\LegacyEnumShimTrait;

/**
 * TODO: These tags need to be removed once we get rid of LegacyEnumShimTrait (PM6)
 *  These are retained for backwards compatibility only.
 *
 * @method static BoatType ACACIA()
 * @method static BoatType BIRCH()
 * @method static BoatType DARK_OAK()
 * @method static BoatType JUNGLE()
 * @method static BoatType MANGROVE()
 * @method static BoatType OAK()
 * @method static BoatType SPRUCE()
 */
enum BoatType
{
	use LegacyEnumShimTrait;

	case OAK;
	case SPRUCE;
	case BIRCH;
	case JUNGLE;
	case ACACIA;
	case DARK_OAK;
	case MANGROVE;

	public function getWoodType() : WoodType
	{
		return match($this) {
			self::OAK => WoodType::OAK,
			self::SPRUCE => WoodType::SPRUCE,
			self::BIRCH => WoodType::BIRCH,
			self::JUNGLE => WoodType::JUNGLE,
			self::ACACIA => WoodType::ACACIA,
			self::DARK_OAK => WoodType::DARK_OAK,
			self::MANGROVE => WoodType::MANGROVE,
		};
	}

	public function getDisplayName() : string
	{
		return $this->getWoodType()->getDisplayName();
	}
}
