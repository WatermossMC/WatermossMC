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

namespace watermossmc\plugin;

final class PluginLoadTriage
{
	/**
	 * @var PluginLoadTriageEntry[]
	 * @phpstan-var array<string, PluginLoadTriageEntry>
	 */
	public array $plugins = [];
	/**
	 * @var string[][]
	 * @phpstan-var array<string, list<string>>
	 */
	public array $dependencies = [];
	/**
	 * @var string[][]
	 * @phpstan-var array<string, list<string>>
	 */
	public array $softDependencies = [];
}
