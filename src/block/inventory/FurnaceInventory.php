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

namespace watermossmc\block\inventory;

use watermossmc\crafting\FurnaceType;
use watermossmc\inventory\SimpleInventory;
use watermossmc\item\Item;
use watermossmc\world\Position;

class FurnaceInventory extends SimpleInventory implements BlockInventory
{
	use BlockInventoryTrait;

	public const SLOT_INPUT = 0;
	public const SLOT_FUEL = 1;
	public const SLOT_RESULT = 2;

	public function __construct(
		Position $holder,
		private FurnaceType $furnaceType
	) {
		$this->holder = $holder;
		parent::__construct(3);
	}

	public function getFurnaceType() : FurnaceType
	{
		return $this->furnaceType;
	}

	public function getResult() : Item
	{
		return $this->getItem(self::SLOT_RESULT);
	}

	public function getFuel() : Item
	{
		return $this->getItem(self::SLOT_FUEL);
	}

	public function getSmelting() : Item
	{
		return $this->getItem(self::SLOT_INPUT);
	}

	public function setResult(Item $item) : void
	{
		$this->setItem(self::SLOT_RESULT, $item);
	}

	public function setFuel(Item $item) : void
	{
		$this->setItem(self::SLOT_FUEL, $item);
	}

	public function setSmelting(Item $item) : void
	{
		$this->setItem(self::SLOT_INPUT, $item);
	}
}
