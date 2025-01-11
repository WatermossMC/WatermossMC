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

use watermossmc\utils\LegacyEnumShimTrait;

/**
 * TODO: These tags need to be removed once we get rid of LegacyEnumShimTrait (PM6)
 *  These are retained for backwards compatibility only.
 *
 * @method static NoteInstrument BANJO()
 * @method static NoteInstrument BASS_DRUM()
 * @method static NoteInstrument BELL()
 * @method static NoteInstrument BIT()
 * @method static NoteInstrument CHIME()
 * @method static NoteInstrument CLICKS_AND_STICKS()
 * @method static NoteInstrument COW_BELL()
 * @method static NoteInstrument DIDGERIDOO()
 * @method static NoteInstrument DOUBLE_BASS()
 * @method static NoteInstrument FLUTE()
 * @method static NoteInstrument GUITAR()
 * @method static NoteInstrument IRON_XYLOPHONE()
 * @method static NoteInstrument PIANO()
 * @method static NoteInstrument PLING()
 * @method static NoteInstrument SNARE()
 * @method static NoteInstrument XYLOPHONE()
 */
enum NoteInstrument
{
	use LegacyEnumShimTrait;

	case PIANO;
	case BASS_DRUM;
	case SNARE;
	case CLICKS_AND_STICKS;
	case DOUBLE_BASS;
	case BELL;
	case FLUTE;
	case CHIME;
	case GUITAR;
	case XYLOPHONE;
	case IRON_XYLOPHONE;
	case COW_BELL;
	case DIDGERIDOO;
	case BIT;
	case BANJO;
	case PLING;
}
