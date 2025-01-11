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

use watermossmc\item\enchantment\Enchantment;
use watermossmc\item\enchantment\VanillaEnchantments;
use watermossmc\utils\SingletonTrait;

/**
 * Handles translation of internal enchantment types to and from Minecraft: Bedrock IDs.
 */
final class EnchantmentIdMap
{
	use SingletonTrait;
	/** @phpstan-use IntSaveIdMapTrait<Enchantment> */
	use IntSaveIdMapTrait;

	private function __construct()
	{
		$this->register(EnchantmentIds::PROTECTION, VanillaEnchantments::PROTECTION());
		$this->register(EnchantmentIds::FIRE_PROTECTION, VanillaEnchantments::FIRE_PROTECTION());
		$this->register(EnchantmentIds::FEATHER_FALLING, VanillaEnchantments::FEATHER_FALLING());
		$this->register(EnchantmentIds::BLAST_PROTECTION, VanillaEnchantments::BLAST_PROTECTION());
		$this->register(EnchantmentIds::PROJECTILE_PROTECTION, VanillaEnchantments::PROJECTILE_PROTECTION());
		$this->register(EnchantmentIds::THORNS, VanillaEnchantments::THORNS());
		$this->register(EnchantmentIds::RESPIRATION, VanillaEnchantments::RESPIRATION());
		$this->register(EnchantmentIds::AQUA_AFFINITY, VanillaEnchantments::AQUA_AFFINITY());

		$this->register(EnchantmentIds::SHARPNESS, VanillaEnchantments::SHARPNESS());
		//TODO: smite, bane of arthropods (these don't make sense now because their applicable mobs don't exist yet)

		$this->register(EnchantmentIds::KNOCKBACK, VanillaEnchantments::KNOCKBACK());
		$this->register(EnchantmentIds::FIRE_ASPECT, VanillaEnchantments::FIRE_ASPECT());

		$this->register(EnchantmentIds::EFFICIENCY, VanillaEnchantments::EFFICIENCY());
		$this->register(EnchantmentIds::FORTUNE, VanillaEnchantments::FORTUNE());
		$this->register(EnchantmentIds::SILK_TOUCH, VanillaEnchantments::SILK_TOUCH());
		$this->register(EnchantmentIds::UNBREAKING, VanillaEnchantments::UNBREAKING());

		$this->register(EnchantmentIds::POWER, VanillaEnchantments::POWER());
		$this->register(EnchantmentIds::PUNCH, VanillaEnchantments::PUNCH());
		$this->register(EnchantmentIds::FLAME, VanillaEnchantments::FLAME());
		$this->register(EnchantmentIds::INFINITY, VanillaEnchantments::INFINITY());

		$this->register(EnchantmentIds::MENDING, VanillaEnchantments::MENDING());

		$this->register(EnchantmentIds::VANISHING, VanillaEnchantments::VANISHING());

		$this->register(EnchantmentIds::SWIFT_SNEAK, VanillaEnchantments::SWIFT_SNEAK());

		$this->register(EnchantmentIds::FROST_WALKER, VanillaEnchantments::FROST_WALKER());
	}
}
