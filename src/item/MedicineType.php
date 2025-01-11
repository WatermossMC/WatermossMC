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

namespace watermossmc\item;

use watermossmc\entity\effect\Effect;
use watermossmc\entity\effect\VanillaEffects;
use watermossmc\utils\LegacyEnumShimTrait;

/**
 * TODO: These tags need to be removed once we get rid of LegacyEnumShimTrait (PM6)
 *  These are retained for backwards compatibility only.
 *
 * @method static MedicineType ANTIDOTE()
 * @method static MedicineType ELIXIR()
 * @method static MedicineType EYE_DROPS()
 * @method static MedicineType TONIC()
 */
enum MedicineType
{
	use LegacyEnumShimTrait;

	case ANTIDOTE;
	case ELIXIR;
	case EYE_DROPS;
	case TONIC;

	/**
	 * @phpstan-return array{0: string, 1: Effect}
	 */
	private function getMetadata() : array
	{
		//cache not required here - VanillaEffects always returns the same object
		return match($this) {
			self::ANTIDOTE => ['Antidote', VanillaEffects::POISON()],
			self::ELIXIR => ['Elixir', VanillaEffects::WEAKNESS()],
			self::EYE_DROPS => ['Eye Drops', VanillaEffects::BLINDNESS()],
			self::TONIC => ['Tonic', VanillaEffects::NAUSEA()]
		};
	}

	public function getDisplayName() : string
	{
		return $this->getMetadata()[0];
	}

	public function getCuredEffect() : Effect
	{
		return $this->getMetadata()[1];
	}
}
