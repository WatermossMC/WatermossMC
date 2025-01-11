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
 * @method static MobHeadType CREEPER()
 * @method static MobHeadType DRAGON()
 * @method static MobHeadType PIGLIN()
 * @method static MobHeadType PLAYER()
 * @method static MobHeadType SKELETON()
 * @method static MobHeadType WITHER_SKELETON()
 * @method static MobHeadType ZOMBIE()
 */
enum MobHeadType
{
	use LegacyEnumShimTrait;

	case SKELETON;
	case WITHER_SKELETON;
	case ZOMBIE;
	case PLAYER;
	case CREEPER;
	case DRAGON;
	case PIGLIN;

	public function getDisplayName() : string
	{
		return match($this) {
			self::SKELETON => "Skeleton Skull",
			self::WITHER_SKELETON => "Wither Skeleton Skull",
			self::ZOMBIE => "Zombie Head",
			self::PLAYER => "Player Head",
			self::CREEPER => "Creeper Head",
			self::DRAGON => "Dragon Head",
			self::PIGLIN => "Piglin Head"
		};
	}
}
