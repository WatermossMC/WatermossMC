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

namespace watermossmc\entity;

use watermossmc\item\Item;
use watermossmc\item\VanillaItems;
use watermossmc\nbt\tag\CompoundTag;
use watermossmc\network\mcpe\protocol\types\entity\EntityIds;
use watermossmc\network\mcpe\protocol\types\entity\EntityMetadataCollection;
use watermossmc\network\mcpe\protocol\types\entity\EntityMetadataFlags;
use watermossmc\network\mcpe\protocol\types\entity\EntityMetadataProperties;

class Villager extends Living implements Ageable
{
	public const PROFESSION_FARMER = 0;
	public const PROFESSION_LIBRARIAN = 1;
	public const PROFESSION_PRIEST = 2;
	public const PROFESSION_BLACKSMITH = 3;
	public const PROFESSION_BUTCHER = 4;

	private const TAG_PROFESSION = "Profession"; //TAG_Int

	public static function getNetworkTypeId() : string
	{
		return EntityIds::VILLAGER;
	}

	private bool $baby = false;
	private int $profession = self::PROFESSION_FARMER;

	protected function getInitialSizeInfo() : EntitySizeInfo
	{
		return new EntitySizeInfo(1.8, 0.6); //TODO: eye height??
	}

	public function getName() : string
	{
		return "Villager";
	}

	protected function initEntity(CompoundTag $nbt) : void
	{
		parent::initEntity($nbt);

		/** @var int $profession */
		$profession = $nbt->getInt(self::TAG_PROFESSION, self::PROFESSION_FARMER);

		if ($profession > 4 || $profession < 0) {
			$profession = self::PROFESSION_FARMER;
		}

		$this->setProfession($profession);
	}

	public function saveNBT() : CompoundTag
	{
		$nbt = parent::saveNBT();
		$nbt->setInt(self::TAG_PROFESSION, $this->getProfession());

		return $nbt;
	}

	/**
	 * Sets the villager profession
	 */
	public function setProfession(int $profession) : void
	{
		$this->profession = $profession; //TODO: validation
		$this->networkPropertiesDirty = true;
	}

	public function getProfession() : int
	{
		return $this->profession;
	}

	public function isBaby() : bool
	{
		return $this->baby;
	}

	public function getPickedItem() : ?Item
	{
		return VanillaItems::VILLAGER_SPAWN_EGG();
	}

	protected function syncNetworkData(EntityMetadataCollection $properties) : void
	{
		parent::syncNetworkData($properties);
		$properties->setGenericFlag(EntityMetadataFlags::BABY, $this->baby);

		$properties->setInt(EntityMetadataProperties::VARIANT, $this->profession);
	}
}
