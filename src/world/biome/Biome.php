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

namespace watermossmc\world\biome;

use watermossmc\block\Block;
use watermossmc\utils\Random;
use watermossmc\world\ChunkManager;
use watermossmc\world\generator\populator\Populator;

abstract class Biome
{
	public const MAX_BIOMES = 256;

	private int $id;
	private bool $registered = false;

	/** @var Populator[] */
	private array $populators = [];

	private int $minElevation;
	private int $maxElevation;

	/** @var Block[] */
	private array $groundCover = [];

	protected float $rainfall = 0.5;
	protected float $temperature = 0.5;

	public function clearPopulators() : void
	{
		$this->populators = [];
	}

	public function addPopulator(Populator $populator) : void
	{
		$this->populators[] = $populator;
	}

	public function populateChunk(ChunkManager $world, int $chunkX, int $chunkZ, Random $random) : void
	{
		foreach ($this->populators as $populator) {
			$populator->populate($world, $chunkX, $chunkZ, $random);
		}
	}

	/**
	 * @return Populator[]
	 */
	public function getPopulators() : array
	{
		return $this->populators;
	}

	public function setId(int $id) : void
	{
		if (!$this->registered) {
			$this->registered = true;
			$this->id = $id;
		}
	}

	public function getId() : int
	{
		return $this->id;
	}

	abstract public function getName() : string;

	public function getMinElevation() : int
	{
		return $this->minElevation;
	}

	public function getMaxElevation() : int
	{
		return $this->maxElevation;
	}

	public function setElevation(int $min, int $max) : void
	{
		$this->minElevation = $min;
		$this->maxElevation = $max;
	}

	/**
	 * @return Block[]
	 */
	public function getGroundCover() : array
	{
		return $this->groundCover;
	}

	/**
	 * @param Block[] $covers
	 */
	public function setGroundCover(array $covers) : void
	{
		$this->groundCover = $covers;
	}

	public function getTemperature() : float
	{
		return $this->temperature;
	}

	public function getRainfall() : float
	{
		return $this->rainfall;
	}
}
