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

namespace watermossmc\world\generator;

use watermossmc\utils\Random;
use watermossmc\utils\Utils;
use watermossmc\world\ChunkManager;

use function preg_match;

abstract class Generator
{
	/**
	 * Converts a string world seed into an integer for use by the generator.
	 */
	public static function convertSeed(string $seed) : ?int
	{
		if ($seed === "") { //empty seed should cause a random seed to be selected - can't use 0 here because 0 is a valid seed
			$convertedSeed = null;
		} elseif (preg_match('/^-?\d+$/', $seed) === 1) { //this avoids treating seeds like "404.4" as integer seeds
			$convertedSeed = (int) $seed;
		} else {
			$convertedSeed = Utils::javaStringHash($seed);
		}

		return $convertedSeed;
	}

	protected Random $random;

	public function __construct(
		protected int $seed,
		protected string $preset
	) {
		$this->random = new Random($seed);
	}

	abstract public function generateChunk(ChunkManager $world, int $chunkX, int $chunkZ) : void;

	abstract public function populateChunk(ChunkManager $world, int $chunkX, int $chunkZ) : void;
}
