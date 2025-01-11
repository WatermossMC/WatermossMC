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

namespace watermossmc\entity;

use watermossmc\math\Vector3;
use watermossmc\world\Position;
use watermossmc\world\World;

class Location extends Position
{
	public float $yaw;
	public float $pitch;

	public function __construct(float $x, float $y, float $z, ?World $world, float $yaw, float $pitch)
	{
		$this->yaw = $yaw;
		$this->pitch = $pitch;
		parent::__construct($x, $y, $z, $world);
	}

	/**
	 * @return Location
	 */
	public static function fromObject(Vector3 $pos, ?World $world, float $yaw = 0.0, float $pitch = 0.0)
	{
		return new Location($pos->x, $pos->y, $pos->z, $world ?? (($pos instanceof Position) ? $pos->world : null), $yaw, $pitch);
	}

	/**
	 * Return a Location instance
	 */
	public function asLocation() : Location
	{
		return new Location($this->x, $this->y, $this->z, $this->world, $this->yaw, $this->pitch);
	}

	public function getYaw() : float
	{
		return $this->yaw;
	}

	public function getPitch() : float
	{
		return $this->pitch;
	}

	public function __toString()
	{
		return "Location (world=" . ($this->isValid() ? $this->getWorld()->getDisplayName() : "null") . ", x=$this->x, y=$this->y, z=$this->z, yaw=$this->yaw, pitch=$this->pitch)";
	}

	public function equals(Vector3 $v) : bool
	{
		if ($v instanceof Location) {
			return parent::equals($v) && $v->yaw == $this->yaw && $v->pitch == $this->pitch;
		}
		return parent::equals($v);
	}
}
