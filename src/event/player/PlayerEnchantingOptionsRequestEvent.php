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
