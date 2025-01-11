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

namespace watermossmc\block\tile;

use watermossmc\block\inventory\BarrelInventory;
use watermossmc\math\Vector3;
use watermossmc\nbt\tag\CompoundTag;
use watermossmc\world\World;

class Barrel extends Spawnable implements Container, Nameable
{
	use NameableTrait;
	use ContainerTrait;

	protected BarrelInventory $inventory;

	public function __construct(World $world, Vector3 $pos)
	{
		parent::__construct($world, $pos);
		$this->inventory = new BarrelInventory($this->position);
	}

	public function readSaveData(CompoundTag $nbt) : void
	{
		$this->loadName($nbt);
		$this->loadItems($nbt);
	}

	protected function writeSaveData(CompoundTag $nbt) : void
	{
		$this->saveName($nbt);
		$this->saveItems($nbt);
	}

	public function close() : void
	{
		if (!$this->closed) {
			$this->inventory->removeAllViewers();
			parent::close();
		}
	}

	public function getInventory() : BarrelInventory
	{
		return $this->inventory;
	}

	public function getRealInventory() : BarrelInventory
	{
		return $this->inventory;
	}

	public function getDefaultName() : string
	{
		return "Barrel";
	}
}
