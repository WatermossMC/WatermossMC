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

use watermossmc\block\Block;

/**
 * Called when a new block forms, usually as the result of some action.
 * This could be things like obsidian forming due to collision of lava and water.
 */
class BlockFormEvent extends BaseBlockChangeEvent
{
	public function __construct(
		Block $block,
		Block $newState,
		private Block $causingBlock
	) {
		parent::__construct($block, $newState);
	}

	/**
	 * Returns the block which caused the target block to form into a new state.
	 */
	public function getCausingBlock() : Block
	{
		return $this->causingBlock;
	}
}
