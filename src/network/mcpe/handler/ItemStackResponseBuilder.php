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

namespace watermossmc\network\mcpe\handler;

use watermossmc\inventory\Inventory;
use watermossmc\item\Durable;
use watermossmc\network\mcpe\InventoryManager;
use watermossmc\network\mcpe\protocol\types\inventory\ContainerUIIds;
use watermossmc\network\mcpe\protocol\types\inventory\FullContainerName;
use watermossmc\network\mcpe\protocol\types\inventory\stackresponse\ItemStackResponse;
use watermossmc\network\mcpe\protocol\types\inventory\stackresponse\ItemStackResponseContainerInfo;
use watermossmc\network\mcpe\protocol\types\inventory\stackresponse\ItemStackResponseSlotInfo;
use watermossmc\utils\AssumptionFailedError;

final class ItemStackResponseBuilder
{
	/**
	 * @var int[][]
	 * @phpstan-var array<int, array<int, int>>
	 */
	private array $changedSlots = [];

	public function __construct(
		private int $requestId,
		private InventoryManager $inventoryManager
	) {
	}

	public function addSlot(int $containerInterfaceId, int $slotId) : void
	{
		$this->changedSlots[$containerInterfaceId][$slotId] = $slotId;
	}

	/**
	 * @phpstan-return array{Inventory, int}
	 */
	private function getInventoryAndSlot(int $containerInterfaceId, int $slotId) : ?array
	{
		[$windowId, $slotId] = ItemStackContainerIdTranslator::translate($containerInterfaceId, $this->inventoryManager->getCurrentWindowId(), $slotId);
		$windowAndSlot = $this->inventoryManager->locateWindowAndSlot($windowId, $slotId);
		if ($windowAndSlot === null) {
			return null;
		}
		[$inventory, $slot] = $windowAndSlot;
		if (!$inventory->slotExists($slot)) {
			return null;
		}

		return [$inventory, $slot];
	}

	public function build() : ItemStackResponse
	{
		$responseInfosByContainer = [];
		foreach ($this->changedSlots as $containerInterfaceId => $slotIds) {
			if ($containerInterfaceId === ContainerUIIds::CREATED_OUTPUT) {
				continue;
			}
			foreach ($slotIds as $slotId) {
				$inventoryAndSlot = $this->getInventoryAndSlot($containerInterfaceId, $slotId);
				if ($inventoryAndSlot === null) {
					//a plugin may have closed the inventory during an event, or the slot may have been invalid
					continue;
				}
				[$inventory, $slot] = $inventoryAndSlot;

				$itemStackInfo = $this->inventoryManager->getItemStackInfo($inventory, $slot);
				if ($itemStackInfo === null) {
					throw new AssumptionFailedError("ItemStackInfo should never be null for an open inventory");
				}
				$item = $inventory->getItem($slot);

				$responseInfosByContainer[$containerInterfaceId][] = new ItemStackResponseSlotInfo(
					$slotId,
					$slotId,
					$item->getCount(),
					$itemStackInfo->getStackId(),
					$item->getCustomName(),
					$item->getCustomName(),
					$item instanceof Durable ? $item->getDamage() : 0,
				);
			}
		}

		$responseContainerInfos = [];
		foreach ($responseInfosByContainer as $containerInterfaceId => $responseInfos) {
			$responseContainerInfos[] = new ItemStackResponseContainerInfo(new FullContainerName($containerInterfaceId), $responseInfos);
		}

		return new ItemStackResponse(ItemStackResponse::RESULT_OK, $this->requestId, $responseContainerInfos);
	}
}
