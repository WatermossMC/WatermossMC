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

use watermossmc\entity\Living;
use watermossmc\item\Item;
use watermossmc\utils\Utils;

/**
 * @phpstan-extends EntityEvent<Living>
 */
class EntityDeathEvent extends EntityEvent
{
	/**
	 * @param Item[] $drops
	 */
	public function __construct(
		Living $entity,
		private array $drops = [],
		private int $xp = 0
	) {
		$this->entity = $entity;
	}

	/**
	 * @return Living
	 */
	public function getEntity()
	{
		return $this->entity;
	}

	/**
	 * @return Item[]
	 */
	public function getDrops() : array
	{
		return $this->drops;
	}

	/**
	 * @param Item[] $drops
	 */
	public function setDrops(array $drops) : void
	{
		Utils::validateArrayValueType($drops, function (Item $_) : void {});
		$this->drops = $drops;
	}

	/**
	 * Returns how much experience is dropped due to this entity's death.
	 */
	public function getXpDropAmount() : int
	{
		return $this->xp;
	}

	/**
	 * @throws \InvalidArgumentException
	 */
	public function setXpDropAmount(int $xp) : void
	{
		if ($xp < 0) {
			throw new \InvalidArgumentException("XP drop amount must not be negative");
		}
		$this->xp = $xp;
	}
}
