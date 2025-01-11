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

namespace watermossmc\item;

use watermossmc\item\enchantment\VanillaEnchantments;

abstract class Tool extends Durable
{
	public function getMaxStackSize() : int
	{
		return 1;
	}

	public function getMiningEfficiency(bool $isCorrectTool) : float
	{
		$efficiency = 1;
		if ($isCorrectTool) {
			$efficiency = $this->getBaseMiningEfficiency();
			if (($enchantmentLevel = $this->getEnchantmentLevel(VanillaEnchantments::EFFICIENCY())) > 0) {
				$efficiency += ($enchantmentLevel ** 2 + 1);
			}
		}

		return $efficiency;
	}

	protected function getBaseMiningEfficiency() : float
	{
		return 1;
	}
}
