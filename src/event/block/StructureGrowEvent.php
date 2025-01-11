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
use watermossmc\event\Cancellable;
use watermossmc\event\CancellableTrait;
use watermossmc\player\Player;
use watermossmc\world\BlockTransaction;

/**
 * Called when structures such as Saplings or Bamboo grow.
 * These types of plants tend to change multiple blocks at once upon growing.
 */
class StructureGrowEvent extends BlockEvent implements Cancellable
{
	use CancellableTrait;

	public function __construct(
		Block $block,
		private BlockTransaction $transaction,
		private ?Player $player
	) {
		parent::__construct($block);
	}

	public function getTransaction() : BlockTransaction
	{
		return $this->transaction;
	}

	/**
	 * It returns the player which grows the structure.
	 * It returns null when the structure grows by itself.
	 */
	public function getPlayer() : ?Player
	{
		return $this->player;
	}
}
