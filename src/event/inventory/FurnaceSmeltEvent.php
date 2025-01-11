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

namespace watermossmc\event\inventory;

use watermossmc\block\tile\Furnace;
use watermossmc\event\block\BlockEvent;
use watermossmc\event\Cancellable;
use watermossmc\event\CancellableTrait;
use watermossmc\item\Item;

class FurnaceSmeltEvent extends BlockEvent implements Cancellable
{
	use CancellableTrait;

	public function __construct(
		private Furnace $furnace,
		private Item $source,
		private Item $result
	) {
		parent::__construct($furnace->getBlock());
		$this->source = clone $source;
		$this->source->setCount(1);
	}

	public function getFurnace() : Furnace
	{
		return $this->furnace;
	}

	public function getSource() : Item
	{
		return $this->source;
	}

	public function getResult() : Item
	{
		return $this->result;
	}

	public function setResult(Item $result) : void
	{
		$this->result = $result;
	}
}
