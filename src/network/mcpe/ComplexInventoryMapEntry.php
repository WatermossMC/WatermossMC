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

namespace watermossmc\network\mcpe;

use watermossmc\inventory\Inventory;

final class ComplexInventoryMapEntry
{
	/**
	 * @var int[]
	 * @phpstan-var array<int, int>
	 */
	private array $reverseSlotMap = [];

	/**
	 * @param int[] $slotMap
	 * @phpstan-param array<int, int> $slotMap
	 */
	public function __construct(
		private Inventory $inventory,
		private array $slotMap
	) {
		foreach ($slotMap as $slot => $index) {
			$this->reverseSlotMap[$index] = $slot;
		}
	}

	public function getInventory() : Inventory
	{
		return $this->inventory;
	}

	/**
	 * @return int[]
	 * @phpstan-return array<int, int>
	 */
	public function getSlotMap() : array
	{
		return $this->slotMap;
	}

	public function mapNetToCore(int $slot) : ?int
	{
		return $this->slotMap[$slot] ?? null;
	}

	public function mapCoreToNet(int $slot) : ?int
	{
		return $this->reverseSlotMap[$slot] ?? null;
	}
}
