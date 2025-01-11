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

namespace watermossmc\tools\decode_crashdump;

use function array_pop;
use function array_slice;
use function base64_decode;
use function count;
use function file;
use function file_put_contents;
use function fwrite;
use function implode;
use function json_decode;
use function json_encode;
use function realpath;
use function trim;
use function zlib_decode;

use const FILE_IGNORE_NEW_LINES;
use const JSON_PRETTY_PRINT;
use const JSON_UNESCAPED_SLASHES;
use const PHP_EOL;
use const STDERR;

if (count($argv) === 2) {
	$input = $argv[1];
	$output = "decoded.json";
} elseif (count($argv) === 3) {
	[, $input, $output] = $argv;
} else {
	fwrite(STDERR, "Required arguments: input file, output file" . PHP_EOL);
	exit(1);
}

$lines = file($input, FILE_IGNORE_NEW_LINES);
if ($lines === false) {
	fwrite(STDERR, "Unable to read file $input" . PHP_EOL);
	exit(1);
}

$start = -1;
foreach ($lines as $num => $line) {
	if (trim($line) === "===BEGIN CRASH DUMP===") {
		$start = $num + 1;
		break;
	}
}

if ($start === -1) {
	fwrite(STDERR, "Crashdump encoded data not found in target file" . PHP_EOL);
	exit(1);
}

$data = array_slice($lines, $start);
array_pop($data);

$zlibData = base64_decode(implode("", $data), true);
if ($zlibData === false) {
	fwrite(STDERR, "Invalid encoded data in crashdump" . PHP_EOL);
	exit(1);
}
$decoded = zlib_decode($zlibData);
if ($decoded === false) {
	fwrite(STDERR, "Invalid compressed data in crashdump" . PHP_EOL);
	exit(1);
}

file_put_contents($output, json_encode(json_decode($decoded), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
echo "Wrote decoded crashdump to " . realpath($output) . PHP_EOL;
