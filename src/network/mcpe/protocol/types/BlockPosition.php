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

namespace watermossmc\network\mcpe\protocol\types;

use watermossmc\math\Vector3;

final class BlockPosition
{
	public function __construct(
		private int $x,
		private int $y,
		private int $z
	) {
	}

	public function getX() : int
	{
		return $this->x;
	}

	public function getY() : int
	{
		return $this->y;
	}

	public function getZ() : int
	{
		return $this->z;
	}

	public static function fromVector3(Vector3 $vector3) : self
	{
		return new self($vector3->getFloorX(), $vector3->getFloorY(), $vector3->getFloorZ());
	}

	public function equals(BlockPosition $other) : bool
	{
		return $this->x === $other->x && $this->y === $other->y && $this->z === $other->z;
	}
}
