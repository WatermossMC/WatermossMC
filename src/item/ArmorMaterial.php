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

use watermossmc\world\sound\Sound;

class ArmorMaterial
{
	public function __construct(
		private readonly int $enchantability,
		private readonly ?Sound $equipSound = null
	) {
	}

	/**
	 * Returns the value that defines how enchantable the item is.
	 *
	 * The higher an item's enchantability is, the more likely it will be to gain high-level enchantments
	 * or multiple enchantments upon being enchanted in an enchanting table.
	 */
	public function getEnchantability() : int
	{
		return $this->enchantability;
	}

	/**
	 * Returns the sound that plays when equipping the armor
	 */
	public function getEquipSound() : ?Sound
	{
		return $this->equipSound;
	}
}
