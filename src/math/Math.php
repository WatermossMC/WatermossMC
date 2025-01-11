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

namespace watermossmc\math;

use function sqrt;

final class Math
{
	private function __construct()
	{
		//NOOP
	}

	/**
	 * @param float $n
	 */
	public static function floorFloat($n) : int
	{
		$i = (int) $n;
		return $n >= $i ? $i : $i - 1;
	}

	/**
	 * @param float $n
	 */
	public static function ceilFloat($n) : int
	{
		$i = (int) $n;
		return $n <= $i ? $i : $i + 1;
	}

	/**
	 * Solves a quadratic equation with the given coefficients and returns an array of up to two solutions.
	 *
	 * @return float[]
	 */
	public static function solveQuadratic(float $a, float $b, float $c) : array
	{
		if ($a === 0.0) {
			throw new \InvalidArgumentException("Coefficient a cannot be 0!");
		}
		$discriminant = $b * $b - 4 * $a * $c;
		if ($discriminant > 0) { //2 real roots
			$sqrtDiscriminant = sqrt($discriminant);
			return [
				(-$b + $sqrtDiscriminant) / (2 * $a),
				(-$b - $sqrtDiscriminant) / (2 * $a)
			];
		} elseif ($discriminant === 0.0) { //1 real root
			return [
				-$b / (2 * $a)
			];
		} else { //No real roots
			return [];
		}
	}
}
