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

use watermossmc\item\PotionType;
use watermossmc\utils\SingletonTrait;

final class PotionTypeIdMap
{
	use SingletonTrait;
	/** @phpstan-use IntSaveIdMapTrait<PotionType> */
	use IntSaveIdMapTrait;

	private function __construct()
	{
		foreach (PotionType::cases() as $case) {
			$this->register(match($case) {
				PotionType::WATER => PotionTypeIds::WATER,
				PotionType::MUNDANE => PotionTypeIds::MUNDANE,
				PotionType::LONG_MUNDANE => PotionTypeIds::LONG_MUNDANE,
				PotionType::THICK => PotionTypeIds::THICK,
				PotionType::AWKWARD => PotionTypeIds::AWKWARD,
				PotionType::NIGHT_VISION => PotionTypeIds::NIGHT_VISION,
				PotionType::LONG_NIGHT_VISION => PotionTypeIds::LONG_NIGHT_VISION,
				PotionType::INVISIBILITY => PotionTypeIds::INVISIBILITY,
				PotionType::LONG_INVISIBILITY => PotionTypeIds::LONG_INVISIBILITY,
				PotionType::LEAPING => PotionTypeIds::LEAPING,
				PotionType::LONG_LEAPING => PotionTypeIds::LONG_LEAPING,
				PotionType::STRONG_LEAPING => PotionTypeIds::STRONG_LEAPING,
				PotionType::FIRE_RESISTANCE => PotionTypeIds::FIRE_RESISTANCE,
				PotionType::LONG_FIRE_RESISTANCE => PotionTypeIds::LONG_FIRE_RESISTANCE,
				PotionType::SWIFTNESS => PotionTypeIds::SWIFTNESS,
				PotionType::LONG_SWIFTNESS => PotionTypeIds::LONG_SWIFTNESS,
				PotionType::STRONG_SWIFTNESS => PotionTypeIds::STRONG_SWIFTNESS,
				PotionType::SLOWNESS => PotionTypeIds::SLOWNESS,
				PotionType::LONG_SLOWNESS => PotionTypeIds::LONG_SLOWNESS,
				PotionType::WATER_BREATHING => PotionTypeIds::WATER_BREATHING,
				PotionType::LONG_WATER_BREATHING => PotionTypeIds::LONG_WATER_BREATHING,
				PotionType::HEALING => PotionTypeIds::HEALING,
				PotionType::STRONG_HEALING => PotionTypeIds::STRONG_HEALING,
				PotionType::HARMING => PotionTypeIds::HARMING,
				PotionType::STRONG_HARMING => PotionTypeIds::STRONG_HARMING,
				PotionType::POISON => PotionTypeIds::POISON,
				PotionType::LONG_POISON => PotionTypeIds::LONG_POISON,
				PotionType::STRONG_POISON => PotionTypeIds::STRONG_POISON,
				PotionType::REGENERATION => PotionTypeIds::REGENERATION,
				PotionType::LONG_REGENERATION => PotionTypeIds::LONG_REGENERATION,
				PotionType::STRONG_REGENERATION => PotionTypeIds::STRONG_REGENERATION,
				PotionType::STRENGTH => PotionTypeIds::STRENGTH,
				PotionType::LONG_STRENGTH => PotionTypeIds::LONG_STRENGTH,
				PotionType::STRONG_STRENGTH => PotionTypeIds::STRONG_STRENGTH,
				PotionType::WEAKNESS => PotionTypeIds::WEAKNESS,
				PotionType::LONG_WEAKNESS => PotionTypeIds::LONG_WEAKNESS,
				PotionType::WITHER => PotionTypeIds::WITHER,
				PotionType::TURTLE_MASTER => PotionTypeIds::TURTLE_MASTER,
				PotionType::LONG_TURTLE_MASTER => PotionTypeIds::LONG_TURTLE_MASTER,
				PotionType::STRONG_TURTLE_MASTER => PotionTypeIds::STRONG_TURTLE_MASTER,
				PotionType::SLOW_FALLING => PotionTypeIds::SLOW_FALLING,
				PotionType::LONG_SLOW_FALLING => PotionTypeIds::LONG_SLOW_FALLING,
				PotionType::STRONG_SLOWNESS => PotionTypeIds::STRONG_SLOWNESS,
			}, $case);
		}
	}
}
