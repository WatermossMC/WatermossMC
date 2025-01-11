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

use watermossmc\utils\RegistryTrait;
use watermossmc\world\sound\ArmorEquipChainSound;
use watermossmc\world\sound\ArmorEquipDiamondSound;
use watermossmc\world\sound\ArmorEquipGenericSound;
use watermossmc\world\sound\ArmorEquipGoldSound;
use watermossmc\world\sound\ArmorEquipIronSound;
use watermossmc\world\sound\ArmorEquipLeatherSound;
use watermossmc\world\sound\ArmorEquipNetheriteSound;

/**
 * This doc-block is generated automatically, do not modify it manually.
 * This must be regenerated whenever registry members are added, removed or changed.
 * @see build/generate-registry-annotations.php
 * @generate-registry-docblock
 *
 * @method static ArmorMaterial CHAINMAIL()
 * @method static ArmorMaterial DIAMOND()
 * @method static ArmorMaterial GOLD()
 * @method static ArmorMaterial IRON()
 * @method static ArmorMaterial LEATHER()
 * @method static ArmorMaterial NETHERITE()
 * @method static ArmorMaterial TURTLE()
 */
final class VanillaArmorMaterials
{
	use RegistryTrait;

	private function __construct()
	{
		// NOOP
	}

	protected static function register(string $name, ArmorMaterial $armorMaterial) : void
	{
		self::_registryRegister($name, $armorMaterial);
	}

	/**
	 * @return ArmorMaterial[]
	 * @phpstan-return array<string, ArmorMaterial>
	 */
	public static function getAll() : array
	{
		// phpstan doesn't support generic traits yet :(
		/** @var ArmorMaterial[] $result */
		$result = self::_registryGetAll();
		return $result;
	}

	protected static function setup() : void
	{
		self::register("leather", new ArmorMaterial(15, new ArmorEquipLeatherSound()));
		self::register("chainmail", new ArmorMaterial(12, new ArmorEquipChainSound()));
		self::register("iron", new ArmorMaterial(9, new ArmorEquipIronSound()));
		self::register("turtle", new ArmorMaterial(9, new ArmorEquipGenericSound()));
		self::register("gold", new ArmorMaterial(25, new ArmorEquipGoldSound()));
		self::register("diamond", new ArmorMaterial(10, new ArmorEquipDiamondSound()));
		self::register("netherite", new ArmorMaterial(15, new ArmorEquipNetheriteSound()));
	}
}
