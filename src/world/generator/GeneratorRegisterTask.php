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

use watermossmc\scheduler\AsyncTask;
use watermossmc\world\World;

class GeneratorRegisterTask extends AsyncTask
{
	public int $seed;
	public int $worldId;
	public int $worldMinY;
	public int $worldMaxY;

	/**
	 * @phpstan-param class-string<Generator> $generatorClass
	 */
	public function __construct(
		World $world,
		public string $generatorClass,
		public string $generatorSettings
	) {
		$this->seed = $world->getSeed();
		$this->worldId = $world->getId();
		$this->worldMinY = $world->getMinY();
		$this->worldMaxY = $world->getMaxY();
	}

	public function onRun() : void
	{
		/**
		 * @var Generator $generator
		 * @see Generator::__construct()
		 */
		$generator = new $this->generatorClass($this->seed, $this->generatorSettings);
		ThreadLocalGeneratorContext::register(new ThreadLocalGeneratorContext($generator, $this->worldMinY, $this->worldMaxY), $this->worldId);
	}
}
