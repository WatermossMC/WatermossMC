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
