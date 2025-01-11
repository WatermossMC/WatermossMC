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

use watermossmc\event\player\PlayerEnchantingOptionsRequestEvent;
use watermossmc\inventory\SimpleInventory;
use watermossmc\inventory\TemporaryInventory;
use watermossmc\item\enchantment\EnchantingHelper as Helper;
use watermossmc\item\enchantment\EnchantingOption;
use watermossmc\item\Item;
use watermossmc\world\Position;

use function array_values;
use function count;

class EnchantInventory extends SimpleInventory implements BlockInventory, TemporaryInventory
{
	use BlockInventoryTrait;

	public const SLOT_INPUT = 0;
	public const SLOT_LAPIS = 1;

	/**
	 * @var EnchantingOption[] $options
	 * @phpstan-var list<EnchantingOption>
	 */
	private array $options = [];

	public function __construct(Position $holder)
	{
		$this->holder = $holder;
		parent::__construct(2);
	}

	protected function onSlotChange(int $index, Item $before) : void
	{
		if ($index === self::SLOT_INPUT) {
			foreach ($this->viewers as $viewer) {
				$this->options = [];
				$item = $this->getInput();
				$options = Helper::generateOptions($this->holder, $item, $viewer->getEnchantmentSeed());

				$event = new PlayerEnchantingOptionsRequestEvent($viewer, $this, $options);
				$event->call();
				if (!$event->isCancelled() && count($event->getOptions()) > 0) {
					$this->options = array_values($event->getOptions());
					$viewer->getNetworkSession()->getInvManager()?->syncEnchantingTableOptions($this->options);
				}
			}
		}

		parent::onSlotChange($index, $before);
	}

	public function getInput() : Item
	{
		return $this->getItem(self::SLOT_INPUT);
	}

	public function getLapis() : Item
	{
		return $this->getItem(self::SLOT_LAPIS);
	}

	public function getOutput(int $optionId) : ?Item
	{
		$option = $this->getOption($optionId);
		return $option === null ? null : Helper::enchantItem($this->getInput(), $option->getEnchantments());
	}

	public function getOption(int $optionId) : ?EnchantingOption
	{
		return $this->options[$optionId] ?? null;
	}
}
