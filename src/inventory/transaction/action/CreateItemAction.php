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

namespace watermossmc\inventory\transaction\action;

use watermossmc\inventory\transaction\TransactionValidationException;
use watermossmc\item\Item;
use watermossmc\item\VanillaItems;
use watermossmc\player\Player;

/**
 * This action is used by creative players to balance transactions involving the creative inventory menu.
 * The source item is the item being created ("taken" from the creative menu).
 */
class CreateItemAction extends InventoryAction
{
	public function __construct(Item $sourceItem)
	{
		parent::__construct($sourceItem, VanillaItems::AIR());
	}

	public function validate(Player $source) : void
	{
		if ($source->hasFiniteResources()) {
			throw new TransactionValidationException("Player has finite resources, cannot create items");
		}
		if (!$source->getCreativeInventory()->contains($this->sourceItem)) {
			throw new TransactionValidationException("Creative inventory does not contain requested item");
		}
	}

	public function execute(Player $source) : void
	{
		//NOOP
	}
}
