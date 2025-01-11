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

namespace watermossmc\crash;

final class CrashDumpDataGeneral
{
	/**
	 * @param string[] $composer_libraries
	 * @phpstan-param array<string, string> $composer_libraries
	 */
	public function __construct(
		public string $name,
		public string $base_version,
		public int $build,
		public bool $is_dev,
		public int $protocol,
		public string $git,
		public string $uname,
		public string $php,
		public string $zend,
		public string $php_os,
		public string $os,
		public array $composer_libraries,
	) {
	}
}
