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
use watermossmc\player\Player;

/**
 * Called when plants or crops grow.
 */
class BlockGrowEvent extends BaseBlockChangeEvent
{
	public function __construct(
		Block $block,
		Block $newState,
		private ?Player $player = null,
	) {
		parent::__construct($block, $newState);
	}

	/**
	 * It returns the player which grows the crop.
	 * It returns null when the crop grows by itself.
	 */
	public function getPlayer() : ?Player
	{
		return $this->player;
	}
}
