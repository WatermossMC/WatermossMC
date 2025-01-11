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

namespace watermossmc\world\particle;

use watermossmc\math\Vector3;
use watermossmc\network\mcpe\protocol\LevelEventPacket;
use watermossmc\network\mcpe\protocol\types\LevelEvent;

use function abs;

class DragonEggTeleportParticle implements Particle
{
	private int $xDiff;
	private int $yDiff;
	private int $zDiff;

	public function __construct(int $xDiff, int $yDiff, int $zDiff)
	{
		$this->xDiff = self::boundOrThrow($xDiff);
		$this->yDiff = self::boundOrThrow($yDiff);
		$this->zDiff = self::boundOrThrow($zDiff);
	}

	private static function boundOrThrow(int $v) : int
	{
		if ($v < -255 || $v > 255) {
			throw new \InvalidArgumentException("Value must be between -255 and 255");
		}
		return $v;
	}

	public function encode(Vector3 $pos) : array
	{
		$data = ($this->zDiff < 0 ? 1 << 26 : 0) |
			($this->yDiff < 0 ? 1 << 25 : 0) |
			($this->xDiff < 0 ? 1 << 24 : 0) |
			(abs($this->xDiff) << 16) |
			(abs($this->yDiff) << 8) |
			abs($this->zDiff);

		return [LevelEventPacket::create(LevelEvent::PARTICLE_DRAGON_EGG_TELEPORT, $data, $pos)];
	}
}
