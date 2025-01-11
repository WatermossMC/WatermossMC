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

/**
 * Class representing a ray trace collision with an AxisAlignedBB
 */
class RayTraceResult
{
	/**
	 * @param int $hitFace one of the Facing::* constants
	 */
	public function __construct(
		public AxisAlignedBB $bb,
		public int $hitFace,
		public Vector3 $hitVector
	) {
	}

	public function getBoundingBox() : AxisAlignedBB
	{
		return $this->bb;
	}

	public function getHitFace() : int
	{
		return $this->hitFace;
	}

	public function getHitVector() : Vector3
	{
		return $this->hitVector;
	}
}
