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

use watermossmc\world\format\io\exception\CorruptedWorldException;
use watermossmc\world\format\io\exception\UnsupportedWorldFormatException;

/**
 * @phpstan-type IsValid \Closure(string $path) : bool
 */
abstract class WorldProviderManagerEntry
{
	/** @phpstan-param IsValid $isValid */
	protected function __construct(
		protected \Closure $isValid
	) {
	}

	/**
	 * Tells if the path is a valid world.
	 * This must tell if the current format supports opening the files in the directory
	 */
	public function isValid(string $path) : bool
	{
		return ($this->isValid)($path);
	}

	/**
	 * @throws CorruptedWorldException
	 * @throws UnsupportedWorldFormatException
	 */
	abstract public function fromPath(string $path, \Logger $logger) : WorldProvider;
}
