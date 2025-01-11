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

namespace watermossmc\entity\utils;

use watermossmc\math\Math;
use watermossmc\utils\AssumptionFailedError;

use function count;
use function max;

abstract class ExperienceUtils
{
	/**
	 * Calculates and returns the amount of XP needed to get from level 0 to level $level
	 */
	public static function getXpToReachLevel(int $level) : int
	{
		if ($level <= 16) {
			return $level ** 2 + $level * 6;
		} elseif ($level <= 31) {
			return (int) ($level ** 2 * 2.5 - 40.5 * $level + 360);
		}

		return (int) ($level ** 2 * 4.5 - 162.5 * $level + 2220);
	}

	/**
	 * Returns the amount of XP needed to reach $level + 1.
	 */
	public static function getXpToCompleteLevel(int $level) : int
	{
		if ($level <= 15) {
			return 2 * $level + 7;
		} elseif ($level <= 30) {
			return 5 * $level - 38;
		} else {
			return 9 * $level - 158;
		}
	}

	/**
	 * Calculates and returns the number of XP levels the specified amount of XP points are worth.
	 * This returns a floating-point number, the decimal part being the progress through the resulting level.
	 */
	public static function getLevelFromXp(int $xp) : float
	{
		if ($xp < 0) {
			throw new \InvalidArgumentException("XP must be at least 0");
		}
		if ($xp <= self::getXpToReachLevel(16)) {
			$a = 1;
			$b = 6;
			$c = 0;
		} elseif ($xp <= self::getXpToReachLevel(31)) {
			$a = 2.5;
			$b = -40.5;
			$c = 360;
		} else {
			$a = 4.5;
			$b = -162.5;
			$c = 2220;
		}

		$x = Math::solveQuadratic($a, $b, $c - $xp);
		if (count($x) === 0) {
			throw new AssumptionFailedError("Expected at least 1 solution");
		}

		return max($x); //we're only interested in the positive solution
	}
}
