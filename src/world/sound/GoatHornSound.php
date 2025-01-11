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

use watermossmc\item\GoatHornType;
use watermossmc\math\Vector3;
use watermossmc\network\mcpe\protocol\LevelSoundEventPacket;
use watermossmc\network\mcpe\protocol\types\LevelSoundEvent;

class GoatHornSound implements Sound
{
	public function __construct(private GoatHornType $goatHornType)
	{
	}

	public function encode(Vector3 $pos) : array
	{
		return [LevelSoundEventPacket::nonActorSound(match($this->goatHornType) {
			GoatHornType::PONDER => LevelSoundEvent::HORN_CALL0,
			GoatHornType::SING => LevelSoundEvent::HORN_CALL1,
			GoatHornType::SEEK => LevelSoundEvent::HORN_CALL2,
			GoatHornType::FEEL => LevelSoundEvent::HORN_CALL3,
			GoatHornType::ADMIRE => LevelSoundEvent::HORN_CALL4,
			GoatHornType::CALL => LevelSoundEvent::HORN_CALL5,
			GoatHornType::YEARN => LevelSoundEvent::HORN_CALL6,
			GoatHornType::DREAM => LevelSoundEvent::HORN_CALL7
		}, $pos, false)];
	}
}
