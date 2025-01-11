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
use watermossmc\math\Vector3;
use watermossmc\utils\Utils;

class BlockTeleportEvent extends BlockEvent implements Cancellable
{
	use CancellableTrait;

	public function __construct(
		Block $block,
		private Vector3 $to
	) {
		parent::__construct($block);
	}

	public function getTo() : Vector3
	{
		return $this->to;
	}

	public function setTo(Vector3 $to) : void
	{
		Utils::checkVector3NotInfOrNaN($to);
		$this->to = $to;
	}
}
