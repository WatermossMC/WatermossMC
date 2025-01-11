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

namespace watermossmc\block;

use watermossmc\block\utils\SupportType;
use watermossmc\item\Item;

use function mt_rand;

class MonsterSpawner extends Transparent
{
	public function getDropsForCompatibleTool(Item $item) : array
	{
		return [];
	}

	protected function getXpDropAmount() : int
	{
		return mt_rand(15, 43);
	}

	public function onScheduledUpdate() : void
	{
		//TODO
	}

	public function getSupportType(int $facing) : SupportType
	{
		return SupportType::NONE;
	}
}
