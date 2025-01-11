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
use watermossmc\world\generator\object\TreeType;

/**
 * TODO: These tags need to be removed once we get rid of LegacyEnumShimTrait (PM6)
 *  These are retained for backwards compatibility only.
 *
 * @method static SaplingType ACACIA()
 * @method static SaplingType BIRCH()
 * @method static SaplingType DARK_OAK()
 * @method static SaplingType JUNGLE()
 * @method static SaplingType OAK()
 * @method static SaplingType SPRUCE()
 */
enum SaplingType
{
	use LegacyEnumShimTrait;

	case OAK;
	case SPRUCE;
	case BIRCH;
	case JUNGLE;
	case ACACIA;
	case DARK_OAK;
	//TODO: cherry

	public function getTreeType() : TreeType
	{
		return match($this) {
			self::OAK => TreeType::OAK,
			self::SPRUCE => TreeType::SPRUCE,
			self::BIRCH => TreeType::BIRCH,
			self::JUNGLE => TreeType::JUNGLE,
			self::ACACIA => TreeType::ACACIA,
			self::DARK_OAK => TreeType::DARK_OAK,
		};
	}

	public function getDisplayName() : string
	{
		return $this->getTreeType()->getDisplayName();
	}
}
