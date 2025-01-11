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

use function intdiv;
use function min;

class XpLevelUpSound implements Sound
{
	public function __construct(private int $xpLevel)
	{
	}

	public function getXpLevel() : int
	{
		return $this->xpLevel;
	}

	public function encode(Vector3 $pos) : array
	{
		//No idea why such odd numbers, but this works...
		//TODO: check arbitrary volume
		return [LevelSoundEventPacket::nonActorSound(LevelSoundEvent::LEVELUP, $pos, false, 0x10000000 * intdiv(min(30, $this->xpLevel), 5))];
	}
}
