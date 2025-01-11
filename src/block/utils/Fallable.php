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

namespace watermossmc\block\utils;

use watermossmc\block\Block;
use watermossmc\entity\object\FallingBlock;
use watermossmc\world\sound\Sound;

interface Fallable
{
	/**
	 * Called every tick by FallingBlock to update the falling state of this block. Used by concrete to check when it
	 * hits water.
	 * Return null if you don't want to change the usual behaviour.
	 */
	public function tickFalling() : ?Block;

	/**
	 * Called when FallingBlock hits the ground.
	 * Returns whether the block should be placed.
	 */
	public function onHitGround(FallingBlock $blockEntity) : bool;

	/**
	 * Returns the damage caused per fallen block. This is multiplied by the fall distance (and capped according to
	 * {@link Fallable::getMaxFallDamage()}) to calculate the damage dealt to any entities who intersect with the block
	 * when it hits the ground.
	 */
	public function getFallDamagePerBlock() : float;

	/**
	 * Returns the maximum damage the block can deal to an entity when it hits the ground.
	 */
	public function getMaxFallDamage() : float;

	/**
	 * Returns the sound that will be played when FallingBlock hits the ground.
	 */
	public function getLandSound() : ?Sound;
}
