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
 * @method static CoralType BRAIN()
 * @method static CoralType BUBBLE()
 * @method static CoralType FIRE()
 * @method static CoralType HORN()
 * @method static CoralType TUBE()
 */
enum CoralType
{
	use LegacyEnumShimTrait;

	case TUBE;
	case BRAIN;
	case BUBBLE;
	case FIRE;
	case HORN;

	public function getDisplayName() : string
	{
		return match($this) {
			self::TUBE => "Tube",
			self::BRAIN => "Brain",
			self::BUBBLE => "Bubble",
			self::FIRE => "Fire",
			self::HORN => "Horn",
		};
	}
}
