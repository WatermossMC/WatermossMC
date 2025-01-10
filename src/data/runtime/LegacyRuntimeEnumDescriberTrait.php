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

namespace watermossmc\data\runtime;

/**
 * Provides backwards-compatible shims for the old codegen'd enum describer methods.
 * This is kept for plugin backwards compatibility, but these functions should not be used in new code.
 * @deprecated
 */
trait LegacyRuntimeEnumDescriberTrait
{
	abstract protected function enum(\UnitEnum &$case) : void;

	public function bellAttachmentType(\watermossmc\block\utils\BellAttachmentType &$value) : void
	{
		$this->enum($value);
	}

	public function copperOxidation(\watermossmc\block\utils\CopperOxidation &$value) : void
	{
		$this->enum($value);
	}

	public function coralType(\watermossmc\block\utils\CoralType &$value) : void
	{
		$this->enum($value);
	}

	public function dirtType(\watermossmc\block\utils\DirtType &$value) : void
	{
		$this->enum($value);
	}

	public function dripleafState(\watermossmc\block\utils\DripleafState &$value) : void
	{
		$this->enum($value);
	}

	public function dyeColor(\watermossmc\block\utils\DyeColor &$value) : void
	{
		$this->enum($value);
	}

	public function froglightType(\watermossmc\block\utils\FroglightType &$value) : void
	{
		$this->enum($value);
	}

	public function leverFacing(\watermossmc\block\utils\LeverFacing &$value) : void
	{
		$this->enum($value);
	}

	public function medicineType(\watermossmc\item\MedicineType &$value) : void
	{
		$this->enum($value);
	}

	public function mobHeadType(\watermossmc\block\utils\MobHeadType &$value) : void
	{
		$this->enum($value);
	}

	public function mushroomBlockType(\watermossmc\block\utils\MushroomBlockType &$value) : void
	{
		$this->enum($value);
	}

	public function potionType(\watermossmc\item\PotionType &$value) : void
	{
		$this->enum($value);
	}

	public function slabType(\watermossmc\block\utils\SlabType &$value) : void
	{
		$this->enum($value);
	}

	public function suspiciousStewType(\watermossmc\item\SuspiciousStewType &$value) : void
	{
		$this->enum($value);
	}
}
