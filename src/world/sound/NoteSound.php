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

use watermossmc\data\bedrock\NoteInstrumentIdMap;
use watermossmc\math\Vector3;
use watermossmc\network\mcpe\protocol\LevelSoundEventPacket;
use watermossmc\network\mcpe\protocol\types\LevelSoundEvent;

class NoteSound implements Sound
{
	public function __construct(
		private NoteInstrument $instrument,
		private int $note
	) {
		if ($this->note < 0 || $this->note > 255) {
			throw new \InvalidArgumentException("Note $note is outside accepted range");
		}
	}

	public function encode(Vector3 $pos) : array
	{
		$instrumentId = NoteInstrumentIdMap::getInstance()->toId($this->instrument);
		return [LevelSoundEventPacket::nonActorSound(LevelSoundEvent::NOTE, $pos, false, ($instrumentId << 8) | $this->note)];
	}
}
