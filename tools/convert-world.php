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

namespace watermossmc\tools\convert_world;

use watermossmc\world\format\io\FormatConverter;
use watermossmc\world\format\io\WorldProviderManager;
use watermossmc\world\format\io\WorldProviderManagerEntry;
use watermossmc\world\format\io\WritableWorldProviderManagerEntry;

use function array_filter;
use function array_key_exists;
use function array_keys;
use function array_map;
use function array_shift;
use function count;
use function dirname;
use function fwrite;
use function getopt;
use function implode;
use function is_dir;
use function is_string;
use function is_writable;
use function mkdir;
use function realpath;

use const PHP_EOL;
use const STDERR;

require_once dirname(__DIR__) . '/vendor/autoload.php';

$providerManager = new WorldProviderManager();
$writableFormats = array_filter($providerManager->getAvailableProviders(), fn (WorldProviderManagerEntry $class) => $class instanceof WritableWorldProviderManagerEntry);
$requiredOpts = [
	"world" => "path to the input world for conversion",
	"backup" => "path to back up the original files",
	"format" => "desired output format (can be one of: " . implode(",", array_keys($writableFormats)) . ")"
];
$usageMessage = "Options:\n";
foreach ($requiredOpts as $_opt => $_desc) {
	$usageMessage .= "\t--$_opt : $_desc\n";
}
$plainArgs = getopt("", array_map(function (string $str) { return "$str:"; }, array_keys($requiredOpts)));
$args = [];
foreach ($requiredOpts as $opt => $desc) {
	if (!isset($plainArgs[$opt]) || !is_string($plainArgs[$opt])) {
		fwrite(STDERR, $usageMessage);
		exit(1);
	}
	$args[$opt] = $plainArgs[$opt];
}
if (!array_key_exists($args["format"], $writableFormats)) {
	fwrite(STDERR, $usageMessage);
	exit(1);
}

$inputPath = realpath($args["world"]);
if ($inputPath === false) {
	fwrite(STDERR, "Cannot find input world at location: " . $args["world"] . PHP_EOL);
	exit(1);
}
$backupPath = realpath($args["backup"]);
if ($backupPath === false || (!@mkdir($backupPath, 0777, true) && !is_dir($backupPath)) || !is_writable($backupPath)) {
	fwrite(STDERR, "Backup file path " . $args["backup"] . " is not writable (permission error or doesn't exist), aborting" . PHP_EOL);
	exit(1);
}

$oldProviderClasses = $providerManager->getMatchingProviders($inputPath);
if (count($oldProviderClasses) === 0) {
	fwrite(STDERR, "Unknown input world format" . PHP_EOL);
	exit(1);
}
if (count($oldProviderClasses) > 1) {
	fwrite(STDERR, "Ambiguous input world format: matched " . count($oldProviderClasses) . " (" . implode(array_keys($oldProviderClasses)) . ")" . PHP_EOL);
	exit(1);
}
$oldProviderClass = array_shift($oldProviderClasses);
$oldProvider = $oldProviderClass->fromPath($inputPath, new \PrefixedLogger(\GlobalLogger::get(), "Old World Provider"));

$converter = new FormatConverter($oldProvider, $writableFormats[$args["format"]], $backupPath, \GlobalLogger::get());
$converter->execute();
