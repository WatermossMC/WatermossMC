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

use watermossmc\entity\effect\Effect;
use watermossmc\entity\effect\VanillaEffects;
use watermossmc\utils\SingletonTrait;

final class EffectIdMap
{
	use SingletonTrait;
	/** @phpstan-use IntSaveIdMapTrait<Effect> */
	use IntSaveIdMapTrait;

	private function __construct()
	{
		$this->register(EffectIds::SPEED, VanillaEffects::SPEED());
		$this->register(EffectIds::SLOWNESS, VanillaEffects::SLOWNESS());
		$this->register(EffectIds::HASTE, VanillaEffects::HASTE());
		$this->register(EffectIds::MINING_FATIGUE, VanillaEffects::MINING_FATIGUE());
		$this->register(EffectIds::STRENGTH, VanillaEffects::STRENGTH());
		$this->register(EffectIds::INSTANT_HEALTH, VanillaEffects::INSTANT_HEALTH());
		$this->register(EffectIds::INSTANT_DAMAGE, VanillaEffects::INSTANT_DAMAGE());
		$this->register(EffectIds::JUMP_BOOST, VanillaEffects::JUMP_BOOST());
		$this->register(EffectIds::NAUSEA, VanillaEffects::NAUSEA());
		$this->register(EffectIds::REGENERATION, VanillaEffects::REGENERATION());
		$this->register(EffectIds::RESISTANCE, VanillaEffects::RESISTANCE());
		$this->register(EffectIds::FIRE_RESISTANCE, VanillaEffects::FIRE_RESISTANCE());
		$this->register(EffectIds::WATER_BREATHING, VanillaEffects::WATER_BREATHING());
		$this->register(EffectIds::INVISIBILITY, VanillaEffects::INVISIBILITY());
		$this->register(EffectIds::BLINDNESS, VanillaEffects::BLINDNESS());
		$this->register(EffectIds::NIGHT_VISION, VanillaEffects::NIGHT_VISION());
		$this->register(EffectIds::HUNGER, VanillaEffects::HUNGER());
		$this->register(EffectIds::WEAKNESS, VanillaEffects::WEAKNESS());
		$this->register(EffectIds::POISON, VanillaEffects::POISON());
		$this->register(EffectIds::WITHER, VanillaEffects::WITHER());
		$this->register(EffectIds::HEALTH_BOOST, VanillaEffects::HEALTH_BOOST());
		$this->register(EffectIds::ABSORPTION, VanillaEffects::ABSORPTION());
		$this->register(EffectIds::SATURATION, VanillaEffects::SATURATION());
		$this->register(EffectIds::LEVITATION, VanillaEffects::LEVITATION());
		$this->register(EffectIds::FATAL_POISON, VanillaEffects::FATAL_POISON());
		$this->register(EffectIds::CONDUIT_POWER, VanillaEffects::CONDUIT_POWER());
		//TODO: SLOW_FALLING
		//TODO: BAD_OMEN
		//TODO: VILLAGE_HERO
		$this->register(EffectIds::DARKNESS, VanillaEffects::DARKNESS());
	}
}
