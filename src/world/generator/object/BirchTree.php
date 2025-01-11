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

namespace watermossmc\world\generator\object;

use watermossmc\block\VanillaBlocks;
use watermossmc\utils\Random;
use watermossmc\world\BlockTransaction;
use watermossmc\world\ChunkManager;

class BirchTree extends Tree
{
	public function __construct(
		protected bool $superBirch = false
	) {
		parent::__construct(VanillaBlocks::BIRCH_LOG(), VanillaBlocks::BIRCH_LEAVES());
	}

	public function getBlockTransaction(ChunkManager $world, int $x, int $y, int $z, Random $random) : ?BlockTransaction
	{
		$this->treeHeight = $random->nextBoundedInt(3) + 5;
		if ($this->superBirch) {
			$this->treeHeight += 5;
		}
		return parent::getBlockTransaction($world, $x, $y, $z, $random);
	}
}
