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

use watermossmc\entity\effect\EffectInstance;
use watermossmc\entity\effect\VanillaEffects;
use watermossmc\utils\LegacyEnumShimTrait;

/**
 * TODO: These tags need to be removed once we get rid of LegacyEnumShimTrait (PM6)
 *  These are retained for backwards compatibility only.
 *
 * @method static SuspiciousStewType ALLIUM()
 * @method static SuspiciousStewType AZURE_BLUET()
 * @method static SuspiciousStewType BLUE_ORCHID()
 * @method static SuspiciousStewType CORNFLOWER()
 * @method static SuspiciousStewType DANDELION()
 * @method static SuspiciousStewType LILY_OF_THE_VALLEY()
 * @method static SuspiciousStewType OXEYE_DAISY()
 * @method static SuspiciousStewType POPPY()
 * @method static SuspiciousStewType TULIP()
 * @method static SuspiciousStewType WITHER_ROSE()
 */
enum SuspiciousStewType
{
	use LegacyEnumShimTrait;

	case POPPY;
	case CORNFLOWER;
	case TULIP;
	case AZURE_BLUET;
	case LILY_OF_THE_VALLEY;
	case DANDELION;
	case BLUE_ORCHID;
	case ALLIUM;
	case OXEYE_DAISY;
	case WITHER_ROSE;

	/**
	 * @return EffectInstance[]
	 * @phpstan-return list<EffectInstance>
	 */
	public function getEffects() : array
	{
		return match($this) {
			self::POPPY => [new EffectInstance(VanillaEffects::NIGHT_VISION(), 80)],
			self::CORNFLOWER => [new EffectInstance(VanillaEffects::JUMP_BOOST(), 80)],
			self::TULIP => [new EffectInstance(VanillaEffects::WEAKNESS(), 140)],
			self::AZURE_BLUET => [new EffectInstance(VanillaEffects::BLINDNESS(), 120)],
			self::LILY_OF_THE_VALLEY => [new EffectInstance(VanillaEffects::POISON(), 200)],
			self::DANDELION,
			self::BLUE_ORCHID => [new EffectInstance(VanillaEffects::SATURATION(), 6)],
			self::ALLIUM => [new EffectInstance(VanillaEffects::FIRE_RESISTANCE(), 40)],
			self::OXEYE_DAISY => [new EffectInstance(VanillaEffects::REGENERATION(), 120)],
			self::WITHER_ROSE => [new EffectInstance(VanillaEffects::WITHER(), 120)]
		};
	}
}
