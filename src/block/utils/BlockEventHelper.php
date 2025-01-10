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

namespace watermossmc\block\utils;

use watermossmc\block\Block;
use watermossmc\event\block\BlockDeathEvent;
use watermossmc\event\block\BlockFormEvent;
use watermossmc\event\block\BlockGrowEvent;
use watermossmc\event\block\BlockMeltEvent;
use watermossmc\event\block\BlockSpreadEvent;
use watermossmc\player\Player;

/**
 * Helper class to call block changing events and apply the results to the world.
 * TODO: try to further reduce the amount of code duplication here - while this is much better than before, it's still
 * very repetitive.
 */
final class BlockEventHelper
{
	public static function grow(Block $oldState, Block $newState, ?Player $causingPlayer) : bool
	{
		if (BlockGrowEvent::hasHandlers()) {
			$ev = new BlockGrowEvent($oldState, $newState, $causingPlayer);
			$ev->call();
			if ($ev->isCancelled()) {
				return false;
			}
			$newState = $ev->getNewState();
		}

		$position = $oldState->getPosition();
		$position->getWorld()->setBlock($position, $newState);
		return true;
	}

	public static function spread(Block $oldState, Block $newState, Block $source) : bool
	{
		if (BlockSpreadEvent::hasHandlers()) {
			$ev = new BlockSpreadEvent($oldState, $source, $newState);
			$ev->call();
			if ($ev->isCancelled()) {
				return false;
			}
			$newState = $ev->getNewState();
		}

		$position = $oldState->getPosition();
		$position->getWorld()->setBlock($position, $newState);
		return true;
	}

	public static function form(Block $oldState, Block $newState, Block $cause) : bool
	{
		if (BlockFormEvent::hasHandlers()) {
			$ev = new BlockFormEvent($oldState, $newState, $cause);
			$ev->call();
			if ($ev->isCancelled()) {
				return false;
			}
			$newState = $ev->getNewState();
		}

		$position = $oldState->getPosition();
		$position->getWorld()->setBlock($position, $newState);
		return true;
	}

	public static function melt(Block $oldState, Block $newState) : bool
	{
		if (BlockMeltEvent::hasHandlers()) {
			$ev = new BlockMeltEvent($oldState, $newState);
			$ev->call();
			if ($ev->isCancelled()) {
				return false;
			}
			$newState = $ev->getNewState();
		}

		$position = $oldState->getPosition();
		$position->getWorld()->setBlock($position, $newState);
		return true;
	}

	public static function die(Block $oldState, Block $newState) : bool
	{
		if (BlockDeathEvent::hasHandlers()) {
			$ev = new BlockDeathEvent($oldState, $newState);
			$ev->call();
			if ($ev->isCancelled()) {
				return false;
			}
			$newState = $ev->getNewState();
		}

		$position = $oldState->getPosition();
		$position->getWorld()->setBlock($position, $newState);
		return true;
	}
}
