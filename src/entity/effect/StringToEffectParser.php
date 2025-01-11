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

namespace watermossmc\entity\effect;

use watermossmc\utils\SingletonTrait;
use watermossmc\utils\StringToTParser;

/**
 * Handles parsing effect types from strings. This is used to interpret names in the /effect command.
 *
 * @phpstan-extends StringToTParser<Effect>
 */
final class StringToEffectParser extends StringToTParser
{
	use SingletonTrait;

	private static function make() : self
	{
		$result = new self();

		$result->register("absorption", fn () => VanillaEffects::ABSORPTION());
		$result->register("blindness", fn () => VanillaEffects::BLINDNESS());
		$result->register("conduit_power", fn () => VanillaEffects::CONDUIT_POWER());
		$result->register("darkness", fn () => VanillaEffects::DARKNESS());
		$result->register("fatal_poison", fn () => VanillaEffects::FATAL_POISON());
		$result->register("fire_resistance", fn () => VanillaEffects::FIRE_RESISTANCE());
		$result->register("haste", fn () => VanillaEffects::HASTE());
		$result->register("health_boost", fn () => VanillaEffects::HEALTH_BOOST());
		$result->register("hunger", fn () => VanillaEffects::HUNGER());
		$result->register("instant_damage", fn () => VanillaEffects::INSTANT_DAMAGE());
		$result->register("instant_health", fn () => VanillaEffects::INSTANT_HEALTH());
		$result->register("invisibility", fn () => VanillaEffects::INVISIBILITY());
		$result->register("jump_boost", fn () => VanillaEffects::JUMP_BOOST());
		$result->register("levitation", fn () => VanillaEffects::LEVITATION());
		$result->register("mining_fatigue", fn () => VanillaEffects::MINING_FATIGUE());
		$result->register("nausea", fn () => VanillaEffects::NAUSEA());
		$result->register("night_vision", fn () => VanillaEffects::NIGHT_VISION());
		$result->register("poison", fn () => VanillaEffects::POISON());
		$result->register("regeneration", fn () => VanillaEffects::REGENERATION());
		$result->register("resistance", fn () => VanillaEffects::RESISTANCE());
		$result->register("saturation", fn () => VanillaEffects::SATURATION());
		$result->register("slowness", fn () => VanillaEffects::SLOWNESS());
		$result->register("speed", fn () => VanillaEffects::SPEED());
		$result->register("strength", fn () => VanillaEffects::STRENGTH());
		$result->register("water_breathing", fn () => VanillaEffects::WATER_BREATHING());
		$result->register("weakness", fn () => VanillaEffects::WEAKNESS());
		$result->register("wither", fn () => VanillaEffects::WITHER());

		return $result;
	}

	public function parse(string $input) : ?Effect
	{
		return parent::parse($input);
	}
}
