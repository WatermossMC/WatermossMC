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

namespace watermossmc\item\enchantment;

use watermossmc\entity\Entity;
use watermossmc\entity\Living;

class KnockbackEnchantment extends MeleeWeaponEnchantment
{
	public function isApplicableTo(Entity $victim) : bool
	{
		return $victim instanceof Living;
	}

	public function getDamageBonus(int $enchantmentLevel) : float
	{
		return 0;
	}

	public function onPostAttack(Entity $attacker, Entity $victim, int $enchantmentLevel) : void
	{
		if ($victim instanceof Living) {
			$diff = $victim->getPosition()->subtractVector($attacker->getPosition());
			$victim->knockBack($diff->x, $diff->z, $enchantmentLevel * 0.5);
		}
	}
}
