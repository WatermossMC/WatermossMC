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

use watermossmc\world\WorldCreationOptions;

/**
 * @phpstan-type FromPath \Closure(string $path, \Logger $logger) : WritableWorldProvider
 * @phpstan-type Generate \Closure(string $path, string $name, WorldCreationOptions $options) : void
 */
final class WritableWorldProviderManagerEntry extends WorldProviderManagerEntry
{
	/**
	 * @phpstan-param FromPath $fromPath
	 * @phpstan-param Generate $generate
	 */
	public function __construct(
		\Closure $isValid,
		private \Closure $fromPath,
		private \Closure $generate
	) {
		parent::__construct($isValid);
	}

	public function fromPath(string $path, \Logger $logger) : WritableWorldProvider
	{
		return ($this->fromPath)($path, $logger);
	}

	/**
	 * Generates world manifest files and any other things needed to initialize a new world on disk
	 */
	public function generate(string $path, string $name, WorldCreationOptions $options) : void
	{
		($this->generate)($path, $name, $options);
	}
}
