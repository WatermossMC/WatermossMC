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

namespace watermossmc\item\enchantment;

use watermossmc\utils\SingletonTrait;
use watermossmc\utils\StringToTParser;

/**
 * Handles parsing enchantments from strings. This is used to interpret names in the /enchant command.
 *
 * @phpstan-extends StringToTParser<Enchantment>
 */
final class StringToEnchantmentParser extends StringToTParser
{
	use SingletonTrait;

	private static function make() : self
	{
		$result = new self();

		$result->register("blast_protection", fn () => VanillaEnchantments::BLAST_PROTECTION());
		$result->register("efficiency", fn () => VanillaEnchantments::EFFICIENCY());
		$result->register("feather_falling", fn () => VanillaEnchantments::FEATHER_FALLING());
		$result->register("fire_aspect", fn () => VanillaEnchantments::FIRE_ASPECT());
		$result->register("fire_protection", fn () => VanillaEnchantments::FIRE_PROTECTION());
		$result->register("flame", fn () => VanillaEnchantments::FLAME());
		$result->register("fortune", fn () => VanillaEnchantments::FORTUNE());
		$result->register("frost_walker", fn () => VanillaEnchantments::FROST_WALKER());
		$result->register("infinity", fn () => VanillaEnchantments::INFINITY());
		$result->register("knockback", fn () => VanillaEnchantments::KNOCKBACK());
		$result->register("mending", fn () => VanillaEnchantments::MENDING());
		$result->register("power", fn () => VanillaEnchantments::POWER());
		$result->register("projectile_protection", fn () => VanillaEnchantments::PROJECTILE_PROTECTION());
		$result->register("protection", fn () => VanillaEnchantments::PROTECTION());
		$result->register("punch", fn () => VanillaEnchantments::PUNCH());
		$result->register("respiration", fn () => VanillaEnchantments::RESPIRATION());
		$result->register("aqua_affinity", fn () => VanillaEnchantments::AQUA_AFFINITY());
		$result->register("sharpness", fn () => VanillaEnchantments::SHARPNESS());
		$result->register("silk_touch", fn () => VanillaEnchantments::SILK_TOUCH());
		$result->register("swift_sneak", fn () => VanillaEnchantments::SWIFT_SNEAK());
		$result->register("thorns", fn () => VanillaEnchantments::THORNS());
		$result->register("unbreaking", fn () => VanillaEnchantments::UNBREAKING());
		$result->register("vanishing", fn () => VanillaEnchantments::VANISHING());

		return $result;
	}

	public function parse(string $input) : ?Enchantment
	{
		return parent::parse($input);
	}
}
