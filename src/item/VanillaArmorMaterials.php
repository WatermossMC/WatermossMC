<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.watermossmc.net/
 *
 *
 */

declare(strict_types=1);

/*
 * This file is part of the WatermossMC
 * (c) 2025 WatermossMC <gameplaytebakgambard>
 *
 * @License Apache 2.0
 */

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
