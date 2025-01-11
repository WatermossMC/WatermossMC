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

namespace watermossmc\entity;

use watermossmc\item\Item;
use watermossmc\item\VanillaItems;
use watermossmc\network\mcpe\protocol\types\entity\EntityIds;

use function mt_rand;

class Zombie extends Living
{
	public static function getNetworkTypeId() : string
	{
		return EntityIds::ZOMBIE;
	}

	protected function getInitialSizeInfo() : EntitySizeInfo
	{
		return new EntitySizeInfo(1.8, 0.6); //TODO: eye height ??
	}

	public function getName() : string
	{
		return "Zombie";
	}

	public function getDrops() : array
	{
		$drops = [
			VanillaItems::ROTTEN_FLESH()->setCount(mt_rand(0, 2))
		];

		if (mt_rand(0, 199) < 5) {
			switch (mt_rand(0, 2)) {
				case 0:
					$drops[] = VanillaItems::IRON_INGOT();
					break;
				case 1:
					$drops[] = VanillaItems::CARROT();
					break;
				case 2:
					$drops[] = VanillaItems::POTATO();
					break;
			}
		}

		return $drops;
	}

	public function getXpDropAmount() : int
	{
		//TODO: check for equipment and whether it's a baby
		return 5;
	}

	public function getPickedItem() : ?Item
	{
		return VanillaItems::ZOMBIE_SPAWN_EGG();
	}
}
