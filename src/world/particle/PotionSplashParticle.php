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

use watermossmc\color\Color;
use watermossmc\math\Vector3;
use watermossmc\network\mcpe\protocol\LevelEventPacket;
use watermossmc\network\mcpe\protocol\types\LevelEvent;

class PotionSplashParticle implements Particle
{
	public function __construct(private Color $color)
	{
	}

	/**
	 * Returns the default water-bottle splash colour.
	 *
	 * TODO: replace this with a standard surrogate object constant (first we need to implement them!)
	 */
	public static function DEFAULT_COLOR() : Color
	{
		return new Color(0x38, 0x5d, 0xc6);
	}

	public function getColor() : Color
	{
		return $this->color;
	}

	public function encode(Vector3 $pos) : array
	{
		return [LevelEventPacket::create(LevelEvent::PARTICLE_SPLASH, $this->color->toARGB(), $pos)];
	}
}
