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

use watermossmc\network\mcpe\protocol\ProtocolInfo;
use watermossmc\VersionInfo;

require dirname(__DIR__) . '/vendor/autoload.php';

/*
 * Dumps version info in a machine-readable format for use in GitHub Actions workflows
 */

/**
 * @var string[]|Closure[] $options
 * @phpstan-var array<string, string|Closure() : string> $options
 */
$options = [
	"base_version" => VersionInfo::BASE_VERSION,
	"major_version" => fn () => explode(".", VersionInfo::BASE_VERSION)[0],
	"mcpe_version" => ProtocolInfo::MINECRAFT_VERSION_NETWORK,
	"is_dev" => VersionInfo::IS_DEVELOPMENT_BUILD,
	"changelog_file_name" => function () : string {
		$version = VersionInfo::VERSION();
		$result = $version->getMajor() . "." . $version->getMinor();
		$suffix = $version->getSuffix();
		if ($suffix !== "") {
			if (preg_match('/^([A-Za-z]+)(\d+)$/', $suffix, $matches) !== 1) {
				fwrite(STDERR, "error: invalid current version suffix \"$suffix\"; aborting" . PHP_EOL);
				exit(1);
			}
			$baseSuffix = $matches[1];
			$result .= "-" . strtolower($baseSuffix);
		}
		return $result . ".md";
	},
	"changelog_md_header" => fn () : string => str_replace(".", "", VersionInfo::BASE_VERSION),
	"prerelease" => fn () : bool => VersionInfo::VERSION()->getSuffix() !== "",
	"channel" => VersionInfo::BUILD_CHANNEL,
	"suffix_valid" => function () : bool {
		//TODO: maybe this should be put into its own script?
		$suffix = VersionInfo::VERSION()->getSuffix();
		if (VersionInfo::BUILD_CHANNEL === "stable") {
			//stable builds may not have suffixes
			return $suffix === "";
		}
		if (VersionInfo::BUILD_CHANNEL === "alpha" || VersionInfo::BUILD_CHANNEL === "beta") {
			$upperChannel = strtoupper(VersionInfo::BUILD_CHANNEL);
			$upperSuffix = strtoupper($suffix);
			return str_starts_with($upperSuffix, $upperChannel) && is_numeric(substr($upperSuffix, strlen($upperChannel)));
		}
		return true;
	}
];
if (count($argv) !== 2 || !isset($options[$argv[1]])) {
	fwrite(STDERR, "Please provide an option (one of: " . implode(", ", array_keys($options)) . PHP_EOL);
	exit(1);
}

$result = $options[$argv[1]];
if ($result instanceof Closure) {
	$result = $result();
}
if (is_bool($result)) {
	echo $result ? "true" : "false";
} else {
	echo $result;
}
