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

use watermossmc\block\BaseBanner;

/**
 * Contains information about a pattern layer on a banner.
 * @see BaseBanner
 */
class BannerPatternLayer
{
	public function __construct(
		private BannerPatternType $type,
		private DyeColor $color
	) {
	}

	public function getType() : BannerPatternType
	{
		return $this->type;
	}

	public function getColor() : DyeColor
	{
		return $this->color;
	}
}
