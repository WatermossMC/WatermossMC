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

/*
 * This file is part of the WatermossMC
 * (c) 2025 WatermossMC <gameplaytebakgambard>
 *
 * @License Apache 2.0
 */

namespace watermossmc\event\player;

use watermossmc\block\inventory\EnchantInventory;
use watermossmc\event\Cancellable;
use watermossmc\event\CancellableTrait;
use watermossmc\event\Event;
use watermossmc\item\enchantment\EnchantingOption;
use watermossmc\player\Player;
use watermossmc\utils\Utils;

use function count;

/**
 * Called when a player inserts an item into an enchanting table's input slot.
 * The options provided by the event will be shown on the enchanting table menu.
 */
class PlayerEnchantingOptionsRequestEvent extends PlayerEvent implements Cancellable
{
	use CancellableTrait;

	/**
	 * @param EnchantingOption[] $options
	 */
	public function __construct(
		Player $player,
		private readonly EnchantInventory $inventory,
		private array $options
	) {
		$this->player = $player;
	}

	public function getInventory() : EnchantInventory
	{
		return $this->inventory;
	}

	/**
	 * @return EnchantingOption[]
	 */
	public function getOptions() : array
	{
		return $this->options;
	}

	/**
	 * @param EnchantingOption[] $options
	 */
	public function setOptions(array $options) : void
	{
		Utils::validateArrayValueType($options, function (EnchantingOption $_) : void { });
		if (($optionCount = count($options)) > 3) {
			throw new \LogicException("The maximum number of options for an enchanting table is 3, but $optionCount have been passed");
		}

		$this->options = $options;
	}
}
