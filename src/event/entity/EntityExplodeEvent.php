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
use watermossmc\event\Cancellable;
use watermossmc\event\CancellableTrait;
use watermossmc\utils\Utils;
use watermossmc\world\Position;

/**
 * Called when an entity explodes, after the explosion's impact has been calculated.
 * No changes have been made to the world at this stage.
 *
 * @see EntityPreExplodeEvent
 *
 * @phpstan-extends EntityEvent<Entity>
 */
class EntityExplodeEvent extends EntityEvent implements Cancellable
{
	use CancellableTrait;

	/**
	 * @param Block[] $blocks
	 * @param float   $yield  0-100
	 */
	public function __construct(
		Entity $entity,
		protected Position $position,
		protected array $blocks,
		protected float $yield
	) {
		$this->entity = $entity;
		if ($yield < 0.0 || $yield > 100.0) {
			throw new \InvalidArgumentException("Yield must be in range 0.0 - 100.0");
		}
	}

	public function getPosition() : Position
	{
		return $this->position;
	}

	/**
	 * Returns a list of blocks destroyed by the explosion.
	 *
	 * @return Block[]
	 */
	public function getBlockList() : array
	{
		return $this->blocks;
	}

	/**
	 * Sets the blocks destroyed by the explosion.
	 *
	 * @param Block[] $blocks
	 */
	public function setBlockList(array $blocks) : void
	{
		Utils::validateArrayValueType($blocks, function (Block $_) : void {});
		$this->blocks = $blocks;
	}

	/**
	 * Returns the percentage chance of drops from each block destroyed by the explosion.
	 * @return float 0-100
	 */
	public function getYield() : float
	{
		return $this->yield;
	}

	/**
	 * Sets the percentage chance of drops from each block destroyed by the explosion.
	 * @param float $yield 0-100
	 */
	public function setYield(float $yield) : void
	{
		if ($yield < 0.0 || $yield > 100.0) {
			throw new \InvalidArgumentException("Yield must be in range 0.0 - 100.0");
		}
		$this->yield = $yield;
	}
}
