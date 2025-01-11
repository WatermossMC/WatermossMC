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

enum ChiseledBookshelfSlot : int
{
	case TOP_LEFT = 0;
	case TOP_MIDDLE = 1;
	case TOP_RIGHT = 2;
	case BOTTOM_LEFT = 3;
	case BOTTOM_MIDDLE = 4;
	case BOTTOM_RIGHT = 5;

	private const SLOTS_PER_SHELF = 3;

	public static function fromBlockFaceCoordinates(float $x, float $y) : self
	{
		if ($x < 0 || $x > 1) {
			throw new \InvalidArgumentException("X must be between 0 and 1, got $x");
		}
		if ($y < 0 || $y > 1) {
			throw new \InvalidArgumentException("Y must be between 0 and 1, got $y");
		}

		$slot = ($y < 0.5 ? self::SLOTS_PER_SHELF : 0) + match(true) {
			//we can't use simple maths here as the action is aligned to the 16x16 pixel grid :(
			$x < 6 / 16 => 0,
			$x < 11 / 16 => 1,
			default => 2
		};

		return self::from($slot);
	}
}
