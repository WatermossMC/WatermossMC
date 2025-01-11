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

namespace watermossmc\inventory\transaction;

use watermossmc\inventory\Inventory;
use watermossmc\inventory\transaction\action\InventoryAction;

use function spl_object_id;

final class TransactionBuilder
{
	/** @var TransactionBuilderInventory[] */
	private array $inventories = [];

	/** @var InventoryAction[] */
	private array $extraActions = [];

	public function addAction(InventoryAction $action) : void
	{
		$this->extraActions[spl_object_id($action)] = $action;
	}

	public function getInventory(Inventory $inventory) : TransactionBuilderInventory
	{
		$id = spl_object_id($inventory);
		return $this->inventories[$id] ??= new TransactionBuilderInventory($inventory);
	}

	/**
	 * @return InventoryAction[]
	 */
	public function generateActions() : array
	{
		$actions = $this->extraActions;

		foreach ($this->inventories as $inventory) {
			foreach ($inventory->generateActions() as $action) {
				$actions[spl_object_id($action)] = $action;
			}
		}

		return $actions;
	}
}
