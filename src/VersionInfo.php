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

namespace watermossmc;

use watermossmc\utils\Git;
use watermossmc\utils\VersionString;

use function is_array;
use function is_int;
use function str_repeat;

final class VersionInfo
{
	public const NAME = "WatermossMC";
	public const BASE_VERSION = "5.23.3";
	public const IS_DEVELOPMENT_BUILD = true;
	public const BUILD_CHANNEL = "stable";

	/**
	 * WatermossMC-specific version ID for world data. Used to determine what fixes need to be applied to old world
	 * data (e.g. stuff saved wrongly by past versions).
	 * This version supplements the Minecraft vanilla world version.
	 *
	 * This should be bumped if any **non-Mojang** BC-breaking change or bug fix is made to world save data of any kind
	 * (entities, tiles, blocks, biomes etc.). For example, if PM accidentally saved a block with its facing value
	 * swapped, we would bump this, but not if Mojang did the same change.
	 */
	public const WORLD_DATA_VERSION = 1;
	/**
	 * Name of the NBT tag used to store the world data version.
	 */
	public const TAG_WORLD_DATA_VERSION = "PMMPDataVersion"; //TAG_Long

	private function __construct()
	{
		//NOOP
	}

	private static ?string $gitHash = null;

	public static function GIT_HASH() : string
	{
		if (self::$gitHash === null) {
			$gitHash = str_repeat("00", 20);

			if (\Phar::running(true) === "") {
				$gitHash = Git::getRepositoryStatePretty(\watermossmc\PATH);
			} else {
				$pharPath = \Phar::running(false);
				$phar = \Phar::isValidPharFilename($pharPath) ? new \Phar($pharPath) : new \PharData($pharPath);
				$meta = $phar->getMetadata();
				if (isset($meta["git"])) {
					$gitHash = $meta["git"];
				}
			}

			self::$gitHash = $gitHash;
		}

		return self::$gitHash;
	}

	private static ?int $buildNumber = null;

	public static function BUILD_NUMBER() : int
	{
		if (self::$buildNumber === null) {
			self::$buildNumber = 0;
			if (\Phar::running(true) !== "") {
				$pharPath = \Phar::running(false);
				$phar = \Phar::isValidPharFilename($pharPath) ? new \Phar($pharPath) : new \PharData($pharPath);
				$meta = $phar->getMetadata();
				if (is_array($meta) && isset($meta["build"]) && is_int($meta["build"])) {
					self::$buildNumber = $meta["build"];
				}
			}
		}

		return self::$buildNumber;
	}

	private static ?VersionString $fullVersion = null;

	public static function VERSION() : VersionString
	{
		if (self::$fullVersion === null) {
			self::$fullVersion = new VersionString(self::BASE_VERSION, self::IS_DEVELOPMENT_BUILD, self::BUILD_NUMBER());
		}
		return self::$fullVersion;
	}
}
