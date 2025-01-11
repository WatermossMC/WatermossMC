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

use watermossmc\block\inventory\HopperInventory;
use watermossmc\math\Vector3;
use watermossmc\nbt\tag\CompoundTag;
use watermossmc\world\World;

class Hopper extends Spawnable implements Container, Nameable
{
	use ContainerTrait;
	use NameableTrait;

	private const TAG_TRANSFER_COOLDOWN = "TransferCooldown";

	private HopperInventory $inventory;
	private int $transferCooldown = 0;

	public function __construct(World $world, Vector3 $pos)
	{
		parent::__construct($world, $pos);
		$this->inventory = new HopperInventory($this->position);
	}

	public function readSaveData(CompoundTag $nbt) : void
	{
		$this->loadItems($nbt);
		$this->loadName($nbt);

		$this->transferCooldown = $nbt->getInt(self::TAG_TRANSFER_COOLDOWN, 0);
	}

	protected function writeSaveData(CompoundTag $nbt) : void
	{
		$this->saveItems($nbt);
		$this->saveName($nbt);

		$nbt->setInt(self::TAG_TRANSFER_COOLDOWN, $this->transferCooldown);
	}

	public function close() : void
	{
		if (!$this->closed) {
			$this->inventory->removeAllViewers();

			parent::close();
		}
	}

	public function getDefaultName() : string
	{
		return "Hopper";
	}

	public function getInventory() : HopperInventory
	{
		return $this->inventory;
	}

	public function getRealInventory() : HopperInventory
	{
		return $this->inventory;
	}
}
