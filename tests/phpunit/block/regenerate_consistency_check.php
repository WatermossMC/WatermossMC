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

use watermossmc\block\BlockTest;
use watermossmc\block\RuntimeBlockStateRegistry;

require dirname(__DIR__, 3) . '/vendor/autoload.php';

/* This script needs to be re-run after any intentional blockfactory change (adding or removing a block state). */

[$newTable, $newTiles] = BlockTest::computeConsistencyCheckTable(RuntimeBlockStateRegistry::getInstance());

$oldTablePath = __DIR__ . '/block_factory_consistency_check.json';
if (file_exists($oldTablePath)) {
	$errors = BlockTest::computeConsistencyCheckDiff($oldTablePath, $newTable, $newTiles);

	if (count($errors) > 0) {
		echo count($errors) . " changes detected:\n";
		foreach ($errors as $error) {
			echo $error . "\n";
		}
	} else {
		echo "No changes detected\n";
	}
} else {
	echo "WARNING: Unable to calculate diff, no previous consistency check file found\n";
}

ksort($newTable, SORT_STRING);
ksort($newTiles, SORT_STRING);

file_put_contents($oldTablePath, json_encode(["stateCounts" => $newTable, "tiles" => $newTiles], JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT));
