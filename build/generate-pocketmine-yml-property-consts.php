<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.watermossmc.net/
 *
 *
 */

declare(strict_types=1);

/*
 * This file is part of the WatermossMC
 * (c) 2025 WatermossMC <gameplaytebakgambard>
 *
 * @License Apache 2.0
 */

use watermossmc\utils\Filesystem;
use watermossmc\utils\Utils;

require dirname(__DIR__) . '/vendor/autoload.php';

$defaultConfig = yaml_parse(Filesystem::fileGetContents(dirname(__DIR__) . '/resources/watermossmc.yml'));

if (!is_array($defaultConfig)) {
	fwrite(STDERR, "Invalid default watermossmc.yml\n");
	exit(1);
}

$constants = [];

/**
 * @param mixed[]  $properties
 * @param string[] $constants
 * @phpstan-param array<string, string> $constants
 * @phpstan-param-out array<string, string> $constants
 */
function collectProperties(string $prefix, array $properties, array &$constants) : void
{
	foreach (Utils::promoteKeys($properties) as $propertyName => $property) {
		$fullPropertyName = ($prefix !== "" ? $prefix . "." : "") . $propertyName;

		$constName = str_replace([".", "-"], "_", strtoupper($fullPropertyName));
		$constants[$constName] = $fullPropertyName;

		if (is_array($property)) {
			collectProperties($fullPropertyName, $property, $constants);
		}
	}
}

collectProperties("", $defaultConfig, $constants);
ksort($constants, SORT_STRING);

$file = fopen(dirname(__DIR__) . '/src/YmlServerProperties.php', 'wb');
if ($file === false) {
	fwrite(STDERR, "Failed to open output file\n");
	exit(1);
}
fwrite($file, "<?php\n");
fwrite(
	$file,
	<<<'HEADER'

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.watermossmc.net/
 *
 *
 */


HEADER
);
fwrite($file, "declare(strict_types=1);\n\n");
fwrite($file, "namespace watermossmc;\n\n");

fwrite(
	$file,
	<<<'DOC'
/**
 * @internal
 * Constants for all properties available in watermossmc.yml.
 * This is generated by build/generate-watermossmc-yml-property-consts.php.
 * Do not edit this file manually.
 */

DOC
);
fwrite($file, "final class YmlServerProperties{\n");
fwrite(
	$file,
	<<<'CONSTRUCTOR'

	private function __construct(){
		//NOOP
	}


CONSTRUCTOR
);
foreach (Utils::stringifyKeys($constants) as $constName => $propertyName) {
	fwrite($file, "\tpublic const $constName = '$propertyName';\n");
}
fwrite($file, "}\n");

fclose($file);

echo "Done. Don't forget to run CS fixup after generating code.\n";
