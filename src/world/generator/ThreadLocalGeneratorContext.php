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

/**
 * Manages thread-local caches for generators and the things needed to support them
 */
final class ThreadLocalGeneratorContext
{
	/**
	 * @var self[]
	 * @phpstan-var array<int, self>
	 */
	private static array $contexts = [];

	public static function register(self $context, int $worldId) : void
	{
		self::$contexts[$worldId] = $context;
	}

	public static function unregister(int $worldId) : void
	{
		unset(self::$contexts[$worldId]);
	}

	public static function fetch(int $worldId) : ?self
	{
		return self::$contexts[$worldId] ?? null;
	}

	public function __construct(
		private Generator $generator,
		private int $worldMinY,
		private int $worldMaxY
	) {
	}

	public function getGenerator() : Generator
	{
		return $this->generator;
	}

	public function getWorldMinY() : int
	{
		return $this->worldMinY;
	}

	public function getWorldMaxY() : int
	{
		return $this->worldMaxY;
	}
}
