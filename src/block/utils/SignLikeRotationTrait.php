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

use watermossmc\data\runtime\RuntimeDataDescriber;

use function floor;

trait SignLikeRotationTrait
{
	/** @var int */
	private $rotation = 0;

	protected function describeBlockOnlyState(RuntimeDataDescriber $w) : void
	{
		$w->boundedIntAuto(0, 15, $this->rotation);
	}

	public function getRotation() : int
	{
		return $this->rotation;
	}

	/** @return $this */
	public function setRotation(int $rotation) : self
	{
		if ($rotation < 0 || $rotation > 15) {
			throw new \InvalidArgumentException("Rotation must be in range 0-15");
		}
		$this->rotation = $rotation;
		return $this;
	}

	private static function getRotationFromYaw(float $yaw) : int
	{
		return ((int) floor((($yaw + 180) * 16 / 360) + 0.5)) & 0xf;
	}
}
