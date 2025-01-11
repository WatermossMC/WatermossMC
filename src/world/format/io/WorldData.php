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

namespace watermossmc\world\format\io;

use watermossmc\math\Vector3;

interface WorldData
{
	/**
	 * Saves information about the world state, such as weather, time, etc.
	 */
	public function save() : void;

	public function getName() : string;

	public function setName(string $value) : void;

	/**
	 * Returns the generator name
	 */
	public function getGenerator() : string;

	public function getGeneratorOptions() : string;

	public function getSeed() : int;

	public function getTime() : int;

	public function setTime(int $value) : void;

	public function getSpawn() : Vector3;

	public function setSpawn(Vector3 $pos) : void;

	/**
	 * Returns the world difficulty. This will be one of the World constants.
	 */
	public function getDifficulty() : int;

	/**
	 * Sets the world difficulty.
	 */
	public function setDifficulty(int $difficulty) : void;

	/**
	 * Returns the time in ticks to the next rain level change.
	 */
	public function getRainTime() : int;

	/**
	 * Sets the time in ticks to the next rain level change.
	 */
	public function setRainTime(int $ticks) : void;

	/**
	 * @return float 0.0 - 1.0
	 */
	public function getRainLevel() : float;

	/**
	 * @param float $level 0.0 - 1.0
	 */
	public function setRainLevel(float $level) : void;

	/**
	 * Returns the time in ticks to the next lightning level change.
	 */
	public function getLightningTime() : int;

	/**
	 * Sets the time in ticks to the next lightning level change.
	 */
	public function setLightningTime(int $ticks) : void;

	/**
	 * @return float 0.0 - 1.0
	 */
	public function getLightningLevel() : float;

	/**
	 * @param float $level 0.0 - 1.0
	 */
	public function setLightningLevel(float $level) : void;
}
