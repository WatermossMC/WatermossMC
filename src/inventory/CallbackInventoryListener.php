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

namespace watermossmc\inventory;

use watermossmc\item\Item;
use watermossmc\utils\Utils;

class CallbackInventoryListener implements InventoryListener
{
	//TODO: turn the closure signatures into type aliases when PHPStan supports them

	/**
	 * @phpstan-param (\Closure(Inventory, int, Item) : void)|null $onSlotChange
	 * @phpstan-param (\Closure(Inventory, Item[]) : void)|null $onContentChange
	 */
	public function __construct(
		private ?\Closure $onSlotChange,
		private ?\Closure $onContentChange
	) {
		if ($onSlotChange !== null) {
			Utils::validateCallableSignature(function (Inventory $inventory, int $slot, Item $oldItem) : void {}, $onSlotChange);
		}
		if ($onContentChange !== null) {
			Utils::validateCallableSignature(function (Inventory $inventory, array $oldContents) : void {}, $onContentChange);
		}
	}

	/**
	 * @phpstan-param \Closure(Inventory) : void $onChange
	 */
	public static function onAnyChange(\Closure $onChange) : self
	{
		return new self(
			static function (Inventory $inventory, int $unused, Item $unusedB) use ($onChange) : void {
				$onChange($inventory);
			},
			static function (Inventory $inventory, array $unused) use ($onChange) : void {
				$onChange($inventory);
			}
		);
	}

	public function onSlotChange(Inventory $inventory, int $slot, Item $oldItem) : void
	{
		if ($this->onSlotChange !== null) {
			($this->onSlotChange)($inventory, $slot, $oldItem);
		}
	}

	/**
	 * @param Item[] $oldContents
	 */
	public function onContentChange(Inventory $inventory, array $oldContents) : void
	{
		if ($this->onContentChange !== null) {
			($this->onContentChange)($inventory, $oldContents);
		}
	}
}
