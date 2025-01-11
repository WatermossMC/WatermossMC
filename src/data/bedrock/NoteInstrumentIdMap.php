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

namespace watermossmc\data\bedrock;

use watermossmc\utils\SingletonTrait;
use watermossmc\world\sound\NoteInstrument;

final class NoteInstrumentIdMap
{
	use SingletonTrait;
	/** @phpstan-use IntSaveIdMapTrait<NoteInstrument> */
	use IntSaveIdMapTrait;

	private function __construct()
	{
		foreach (NoteInstrument::cases() as $case) {
			$this->register(match($case) {
				NoteInstrument::PIANO => 0,
				NoteInstrument::BASS_DRUM => 1,
				NoteInstrument::SNARE => 2,
				NoteInstrument::CLICKS_AND_STICKS => 3,
				NoteInstrument::DOUBLE_BASS => 4,
				NoteInstrument::BELL => 5,
				NoteInstrument::FLUTE => 6,
				NoteInstrument::CHIME => 7,
				NoteInstrument::GUITAR => 8,
				NoteInstrument::XYLOPHONE => 9,
				NoteInstrument::IRON_XYLOPHONE => 10,
				NoteInstrument::COW_BELL => 11,
				NoteInstrument::DIDGERIDOO => 12,
				NoteInstrument::BIT => 13,
				NoteInstrument::BANJO => 14,
				NoteInstrument::PLING => 15,
			}, $case);
		}
	}
}
