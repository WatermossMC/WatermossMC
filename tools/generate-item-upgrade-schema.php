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

namespace watermossmc\tools\generate_item_upgrade_schema;

use Symfony\Component\Filesystem\Path;
use watermossmc\errorhandler\ErrorToExceptionHandler;
use watermossmc\utils\Filesystem;

use function count;
use function dirname;
use function file_put_contents;
use function is_array;
use function json_decode;
use function json_encode;
use function ksort;
use function scandir;

use const JSON_FORCE_OBJECT;
use const JSON_PRETTY_PRINT;
use const JSON_THROW_ON_ERROR;
use const SCANDIR_SORT_ASCENDING;
use const SORT_STRING;

require dirname(__DIR__) . '/vendor/autoload.php';

if (count($argv) !== 4) {
	\GlobalLogger::get()->error("Required arguments: path to mapping table, path to current schemas, path to output file");
	exit(1);
}

[, $mappingTableFile, $upgradeSchemasDir, $outputFile] = $argv;

$target = json_decode(Filesystem::fileGetContents($mappingTableFile), true, JSON_THROW_ON_ERROR);
if (!is_array($target)) {
	\GlobalLogger::get()->error("Invalid mapping table file");
	exit(1);
}

$files = ErrorToExceptionHandler::trapAndRemoveFalse(fn () => scandir($upgradeSchemasDir, SCANDIR_SORT_ASCENDING));

$merged = [];
foreach ($files as $file) {
	if ($file === "." || $file === "..") {
		continue;
	}
	\GlobalLogger::get()->info("Processing schema file $file");
	$data = json_decode(Filesystem::fileGetContents(Path::join($upgradeSchemasDir, $file)), associative: true, flags: JSON_THROW_ON_ERROR);
	if (!is_array($data)) {
		\GlobalLogger::get()->error("Invalid schema file $file");
		exit(1);
	}
	foreach (($data["renamedIds"] ?? []) as $oldId => $newId) {
		if (isset($merged["simple"][$oldId])) {
			\GlobalLogger::get()->warning("Duplicate rename for $oldId in file $file (was " . $merged["simple"][$oldId] . ", now $newId)");
		}
		$merged["simple"][$oldId] = $newId;
	}

	foreach (($data["remappedMetas"] ?? []) as $oldId => $mappings) {
		foreach ($mappings as $meta => $newId) {
			if (isset($merged["complex"][$oldId][$meta])) {
				\GlobalLogger::get()->warning("Duplicate meta remap for $oldId meta $meta in file $file (was " . $merged["complex"][$oldId][$meta] . ", now $newId)");
			}
			$merged["complex"][$oldId][$meta] = $newId;
		}
	}
}

$newDiff = [];

foreach ($target["simple"] as $oldId => $newId) {
	$previousNewId = $merged["simple"][$oldId] ?? null;
	if (
		$previousNewId === $newId || //if previous schemas already accounted for this
		($previousNewId !== null && isset($target["simple"][$previousNewId])) //or the item's ID has been changed for a second time
	) {
		continue;
	}
	$newDiff["renamedIds"][$oldId] = $newId;
}
if (isset($newDiff["renamedIds"])) {
	ksort($newDiff["renamedIds"], SORT_STRING);
}

foreach ($target["complex"] as $oldId => $mappings) {
	foreach ($mappings as $meta => $newId) {
		if (($merged["complex"][$oldId][$meta] ?? null) !== $newId) {
			if ($oldId === "minecraft:spawn_egg" && $meta === 130 && ($newId === "minecraft:axolotl_bucket" || $newId === "minecraft:axolotl_spawn_egg")) {
				//TODO: hack for vanilla bug workaround
				continue;
			}
			$newDiff["remappedMetas"][$oldId][$meta] = $newId;
		}
	}
	if (isset($newDiff["remappedMetas"][$oldId])) {
		ksort($newDiff["remappedMetas"][$oldId], SORT_STRING);
	}
}
if (isset($newDiff["remappedMetas"])) {
	ksort($newDiff["remappedMetas"], SORT_STRING);
}
ksort($newDiff, SORT_STRING);

\GlobalLogger::get()->info("Writing output file to $outputFile");
file_put_contents($outputFile, json_encode($newDiff, JSON_PRETTY_PRINT | JSON_FORCE_OBJECT));
