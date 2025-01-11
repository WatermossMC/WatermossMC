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

use watermossmc\utils\AssumptionFailedError;

use function file_exists;
use function fopen;
use function is_dir;
use function rtrim;
use function str_replace;
use function strlen;
use function substr;

use const DIRECTORY_SEPARATOR;

/**
 * Provides resources from the given plugin directory on disk. The path may be prefixed with a specific access protocol
 * to enable special types of access.
 *
 * @deprecated
 */
class DiskResourceProvider implements ResourceProvider
{
	private string $file;

	public function __construct(string $path)
	{
		$this->file = rtrim(str_replace(DIRECTORY_SEPARATOR, "/", $path), "/") . "/";
	}

	/**
	 * Gets an embedded resource on the plugin file.
	 * WARNING: You must close the resource given using fclose()
	 *
	 * @return null|resource Resource data, or null
	 */
	public function getResource(string $filename)
	{
		$filename = rtrim(str_replace(DIRECTORY_SEPARATOR, "/", $filename), "/");
		if (file_exists($this->file . $filename)) {
			$resource = fopen($this->file . $filename, "rb");
			if ($resource === false) {
				throw new AssumptionFailedError("fopen() should not fail on a file which exists");
			}
			return $resource;
		}

		return null;
	}

	/**
	 * Returns all the resources packaged with the plugin in the form ["path/in/resources" => SplFileInfo]
	 *
	 * @return \SplFileInfo[]
	 */
	public function getResources() : array
	{
		$resources = [];
		if (is_dir($this->file)) {
			/** @var \SplFileInfo $resource */
			foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($this->file)) as $resource) {
				if ($resource->isFile()) {
					$path = str_replace(DIRECTORY_SEPARATOR, "/", substr((string) $resource, strlen($this->file)));
					$resources[$path] = $resource;
				}
			}
		}

		return $resources;
	}
}
