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

class ArmorTypeInfo
{
	private ArmorMaterial $material;

	public function __construct(
		private int $defensePoints,
		private int $maxDurability,
		private int $armorSlot,
		private int $toughness = 0,
		private bool $fireProof = false,
		?ArmorMaterial $material = null
	) {
		$this->material = $material ?? VanillaArmorMaterials::LEATHER();
	}

	public function getDefensePoints() : int
	{
		return $this->defensePoints;
	}

	public function getMaxDurability() : int
	{
		return $this->maxDurability;
	}

	public function getArmorSlot() : int
	{
		return $this->armorSlot;
	}

	public function getToughness() : int
	{
		return $this->toughness;
	}

	public function isFireProof() : bool
	{
		return $this->fireProof;
	}

	public function getMaterial() : ArmorMaterial
	{
		return $this->material;
	}
}
