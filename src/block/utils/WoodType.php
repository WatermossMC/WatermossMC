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
 * @method static WoodType ACACIA()
 * @method static WoodType BIRCH()
 * @method static WoodType CHERRY()
 * @method static WoodType CRIMSON()
 * @method static WoodType DARK_OAK()
 * @method static WoodType JUNGLE()
 * @method static WoodType MANGROVE()
 * @method static WoodType OAK()
 * @method static WoodType SPRUCE()
 * @method static WoodType WARPED()
 */
enum WoodType
{
	use LegacyEnumShimTrait;

	case OAK;
	case SPRUCE;
	case BIRCH;
	case JUNGLE;
	case ACACIA;
	case DARK_OAK;
	case MANGROVE;
	case CRIMSON;
	case WARPED;
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
			self::CRIMSON => "Crimson",
			self::WARPED => "Warped",
			self::CHERRY => "Cherry",
		};
	}

	public function isFlammable() : bool
	{
		return $this !== self::CRIMSON && $this !== self::WARPED;
	}

	public function getStandardLogSuffix() : ?string
	{
		return $this === self::CRIMSON || $this === self::WARPED ? "Stem" : null;
	}

	public function getAllSidedLogSuffix() : ?string
	{
		return $this === self::CRIMSON || $this === self::WARPED ? "Hyphae" : null;
	}
}
