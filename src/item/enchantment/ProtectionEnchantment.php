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

use watermossmc\event\entity\EntityDamageEvent;
use watermossmc\lang\Translatable;

use function array_flip;
use function floor;

class ProtectionEnchantment extends Enchantment
{
	protected float $typeModifier;
	/** @var int[]|null */
	protected ?array $applicableDamageTypes = null;

	/**
	 * ProtectionEnchantment constructor.
	 *
	 * @phpstan-param null|(\Closure(int $level) : int) $minEnchantingPower
	 *
	 * @param int        $primaryItemFlags      @deprecated
	 * @param int        $secondaryItemFlags    @deprecated
	 * @param int[]|null $applicableDamageTypes EntityDamageEvent::CAUSE_* constants which this enchantment type applies to, or null if it applies to all types of damage.
	 * @param int        $enchantingPowerRange  Value used to calculate the maximum enchanting power (minEnchantingPower + enchantingPowerRange)
	 */
	public function __construct(Translatable|string $name, int $rarity, int $primaryItemFlags, int $secondaryItemFlags, int $maxLevel, float $typeModifier, ?array $applicableDamageTypes, ?\Closure $minEnchantingPower = null, int $enchantingPowerRange = 50)
	{
		parent::__construct($name, $rarity, $primaryItemFlags, $secondaryItemFlags, $maxLevel, $minEnchantingPower, $enchantingPowerRange);

		$this->typeModifier = $typeModifier;
		if ($applicableDamageTypes !== null) {
			$this->applicableDamageTypes = array_flip($applicableDamageTypes);
		}
	}

	/**
	 * Returns the multiplier by which this enchantment type's EPF increases with each enchantment level.
	 */
	public function getTypeModifier() : float
	{
		return $this->typeModifier;
	}

	/**
	 * Returns the base EPF this enchantment type offers for the given enchantment level.
	 */
	public function getProtectionFactor(int $level) : int
	{
		return (int) floor((6 + $level ** 2) * $this->typeModifier / 3);
	}

	/**
	 * Returns whether this enchantment type offers protection from the specified damage source's cause.
	 */
	public function isApplicable(EntityDamageEvent $event) : bool
	{
		return $this->applicableDamageTypes === null || isset($this->applicableDamageTypes[$event->getCause()]);
	}
}
