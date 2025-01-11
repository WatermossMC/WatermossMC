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

use watermossmc\block\BlockTypeIds;
use watermossmc\entity\Living;
use watermossmc\inventory\transaction\action\validator\CallbackSlotValidator;
use watermossmc\inventory\transaction\TransactionValidationException;
use watermossmc\item\Armor;
use watermossmc\item\Item;
use watermossmc\item\ItemBlock;

class ArmorInventory extends SimpleInventory
{
	public const SLOT_HEAD = 0;
	public const SLOT_CHEST = 1;
	public const SLOT_LEGS = 2;
	public const SLOT_FEET = 3;

	public function __construct(
		protected Living $holder
	) {
		parent::__construct(4);

		$this->validators->add(new CallbackSlotValidator(self::validate(...)));
	}

	public function getHolder() : Living
	{
		return $this->holder;
	}

	public function getHelmet() : Item
	{
		return $this->getItem(self::SLOT_HEAD);
	}

	public function getChestplate() : Item
	{
		return $this->getItem(self::SLOT_CHEST);
	}

	public function getLeggings() : Item
	{
		return $this->getItem(self::SLOT_LEGS);
	}

	public function getBoots() : Item
	{
		return $this->getItem(self::SLOT_FEET);
	}

	public function setHelmet(Item $helmet) : void
	{
		$this->setItem(self::SLOT_HEAD, $helmet);
	}

	public function setChestplate(Item $chestplate) : void
	{
		$this->setItem(self::SLOT_CHEST, $chestplate);
	}

	public function setLeggings(Item $leggings) : void
	{
		$this->setItem(self::SLOT_LEGS, $leggings);
	}

	public function setBoots(Item $boots) : void
	{
		$this->setItem(self::SLOT_FEET, $boots);
	}

	private static function validate(Inventory $inventory, Item $item, int $slot) : ?TransactionValidationException
	{
		if ($item instanceof Armor) {
			if ($item->getArmorSlot() !== $slot) {
				return new TransactionValidationException("Armor item is in wrong slot");
			}
		} else {
			if (!($slot === ArmorInventory::SLOT_HEAD && $item instanceof ItemBlock && (
				$item->getBlock()->getTypeId() === BlockTypeIds::CARVED_PUMPKIN ||
					$item->getBlock()->getTypeId() === BlockTypeIds::MOB_HEAD
			))) {
				return new TransactionValidationException("Item is not accepted in an armor slot");
			}
		}
		return null;
	}
}
