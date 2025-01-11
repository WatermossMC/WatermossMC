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

use watermossmc\thread\ThreadSafeClassLoader;

use function is_file;
use function str_ends_with;

/**
 * Handles different types of plugins
 */
class PharPluginLoader implements PluginLoader
{
	public function __construct(
		private ThreadSafeClassLoader $loader
	) {
	}

	public function canLoadPlugin(string $path) : bool
	{
		return is_file($path) && str_ends_with($path, ".phar");
	}

	/**
	 * Loads the plugin contained in $file
	 */
	public function loadPlugin(string $file) : void
	{
		$description = $this->getPluginDescription($file);
		if ($description !== null) {
			$this->loader->addPath($description->getSrcNamespacePrefix(), "$file/src");
		}
	}

	/**
	 * Gets the PluginDescription from the file
	 */
	public function getPluginDescription(string $file) : ?PluginDescription
	{
		$phar = new \Phar($file);
		if (isset($phar["plugin.yml"])) {
			return new PluginDescription($phar["plugin.yml"]->getContent());
		}

		return null;
	}

	public function getAccessProtocol() : string
	{
		return "phar://";
	}
}
