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
