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

namespace watermossmc\world\sound;

use watermossmc\math\Vector3;
use watermossmc\network\mcpe\protocol\LevelSoundEventPacket;
use watermossmc\network\mcpe\protocol\types\LevelSoundEvent;

final class WaterSplashSound implements Sound
{
	public function __construct(private float $volume)
	{
		if ($volume < 0 || $volume > 1) {
			throw new \InvalidArgumentException("Volume must be between 0 and 1");
		}
	}

	public function encode(Vector3 $pos) : array
	{
		return [LevelSoundEventPacket::create(
			LevelSoundEvent::SPLASH,
			$pos,
			(int) ($this->volume * 16777215),
			":",
			false,
			false
		)];
	}
}
