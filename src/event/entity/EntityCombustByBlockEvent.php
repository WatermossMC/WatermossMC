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

namespace watermossmc\event\entity;

use watermossmc\block\Block;
use watermossmc\entity\Entity;

class EntityCombustByBlockEvent extends EntityCombustEvent
{
	protected Block $combuster;

	public function __construct(Block $combuster, Entity $combustee, int $duration)
	{
		parent::__construct($combustee, $duration);
		$this->combuster = $combuster;
	}

	public function getCombuster() : Block
	{
		return $this->combuster;
	}
}
