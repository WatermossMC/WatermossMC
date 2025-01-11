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

final class CrashDumpDataPluginEntry
{
	/**
	 * @param string[] $authors
	 * @param string[] $api
	 * @param string[] $depends
	 * @param string[] $softDepends
	 */
	public function __construct(
		public string $name,
		public string $version,
		public array $authors,
		public array $api,
		public bool $enabled,
		public array $depends,
		public array $softDepends,
		public string $main,
		public string $load,
		public string $website,
	) {
	}
}
