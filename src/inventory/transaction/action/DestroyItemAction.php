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
 * This action type shows up when a creative player puts an item into the creative inventory menu to destroy it.
 * The output is the item destroyed. You can think of this action type like setting an item into /dev/null
 */
class DestroyItemAction extends InventoryAction
{
	public function __construct(Item $targetItem)
	{
		parent::__construct(VanillaItems::AIR(), $targetItem);
	}

	public function validate(Player $source) : void
	{
		if ($source->hasFiniteResources()) {
			throw new TransactionValidationException("Player has finite resources, cannot destroy items");
		}
	}

	public function execute(Player $source) : void
	{
		//NOOP
	}
}
