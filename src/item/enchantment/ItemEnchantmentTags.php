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

/**
 * Tags used by items and enchantments to determine which enchantments can be applied to which items.
 * Some tags may contain other tags.
 * @see ItemEnchantmentTagRegistry
 */
final class ItemEnchantmentTags
{
	public const ALL = "all";
	public const ARMOR = "armor";
	public const HELMET = "helmet";
	public const CHESTPLATE = "chestplate";
	public const LEGGINGS = "leggings";
	public const BOOTS = "boots";
	public const SHIELD = "shield";
	public const SWORD = "sword";
	public const TRIDENT = "trident";
	public const BOW = "bow";
	public const CROSSBOW = "crossbow";
	public const SHEARS = "shears";
	public const FLINT_AND_STEEL = "flint_and_steel";
	public const BLOCK_TOOLS = "block_tools";
	public const AXE = "axe";
	public const PICKAXE = "pickaxe";
	public const SHOVEL = "shovel";
	public const HOE = "hoe";
	public const FISHING_ROD = "fishing_rod";
	public const CARROT_ON_STICK = "carrot_on_stick";
	public const COMPASS = "compass";
	public const MASK = "mask";
	public const ELYTRA = "elytra";
	public const BRUSH = "brush";
	public const WEAPONS = "weapons";
}
