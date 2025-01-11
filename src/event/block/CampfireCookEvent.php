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

namespace watermossmc\event\block;

use watermossmc\block\Campfire;
use watermossmc\event\Cancellable;
use watermossmc\event\CancellableTrait;
use watermossmc\item\Item;

class CampfireCookEvent extends BlockEvent implements Cancellable
{
	use CancellableTrait;

	public function __construct(
		private Campfire $campfire,
		private int $slot,
		private Item $input,
		private Item $result
	) {
		parent::__construct($campfire);
		$this->input = clone $input;
	}

	public function getCampfire() : Campfire
	{
		return $this->campfire;
	}

	public function getSlot() : int
	{
		return $this->slot;
	}

	public function getInput() : Item
	{
		return $this->input;
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
