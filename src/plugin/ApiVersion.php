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

use watermossmc\utils\VersionString;

use function array_map;
use function array_push;
use function count;
use function usort;

final class ApiVersion
{
	private function __construct()
	{
		//NOOP
	}

	/**
	 * @param string[] $wantVersionsStr
	 */
	public static function isCompatible(string $myVersionStr, array $wantVersionsStr) : bool
	{
		$myVersion = new VersionString($myVersionStr);
		foreach ($wantVersionsStr as $versionStr) {
			$version = new VersionString($versionStr);
			//Format: majorVersion.minorVersion.patch (3.0.0)
			//    or: majorVersion.minorVersion.patch-devBuild (3.0.0-alpha1)
			if ($version->getBaseVersion() !== $myVersion->getBaseVersion()) {
				if ($version->getMajor() !== $myVersion->getMajor()) {
					continue;
				}

				if ($version->getMinor() > $myVersion->getMinor()) { //If the plugin requires new API features, being backwards compatible
					continue;
				}

				if ($version->getMinor() === $myVersion->getMinor() && $version->getPatch() > $myVersion->getPatch()) { //If the plugin requires bug fixes in patches, being backwards compatible
					continue;
				}
			}

			return true;
		}

		return false;
	}

	/**
	 * @param string[] $versions
	 *
	 * @return string[]
	 */
	public static function checkAmbiguousVersions(array $versions) : array
	{
		$indexedVersions = [];

		foreach ($versions as $str) {
			$v = new VersionString($str);
			if ($v->getSuffix() !== "") { //suffix is always unambiguous
				continue;
			}
			if (!isset($indexedVersions[$v->getMajor()])) {
				$indexedVersions[$v->getMajor()] = [$v];
			} else {
				$indexedVersions[$v->getMajor()][] = $v;
			}
		}

		$result = [];
		foreach ($indexedVersions as $list) {
			if (count($list) > 1) {
				array_push($result, ...$list);
			}
		}

		usort($result, static function (VersionString $string1, VersionString $string2) : int { return $string1->compare($string2); });

		return array_map(static function (VersionString $string) : string { return $string->getBaseVersion(); }, $result);
	}
}
