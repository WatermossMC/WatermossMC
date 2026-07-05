<?php

/**
 * generate_item_list.php
 *
 * Usage:
 *   php generate_item_list.php <input.json> [output_dir]
 *
 * Input JSON format (BedrockData required_item_list.json):
 *   {
 *     "minecraft:acacia_boat": {
 *       "runtime_id": 411,
 *       "component_based": false,
 *       "version": 2
 *     },
 *     ...
 *   }
 *
 * Outputs:
 *   ItemTypeList.php  — auto-generated list of all item entries
 *   ItemRegistry.php  — packet sender that uses ItemTypeList
 */

declare(strict_types=1);

$inputFile = $argv[1] ?? null;
$outputDir = rtrim($argv[2] ?? __DIR__, '/');

if ($inputFile === null || !file_exists($inputFile)) {
    fwrite(\STDERR, "Usage: php generate_item_list.php <input.json> [output_dir]\n");
    fwrite(\STDERR, "  input.json  — BedrockData required_item_list.json (or compatible)\n");
    exit(1);
}

$json = file_get_contents($inputFile);
$data = json_decode($json, true, 512, \JSON_THROW_ON_ERROR);

if (!\is_array($data)) {
    fwrite(\STDERR, "ERROR: JSON root must be an object.\n");
    exit(1);
}

$entries = [];
$errors = [];

foreach ($data as $stringId => $props) {
    if (!isset($props['runtime_id'])) {
        $errors[] = "Missing 'runtime_id' for '$stringId'";
        continue;
    }

    $numericId = (int)  $props['runtime_id'];
    $componentBased = (bool) ($props['component_based'] ?? false);
    $version = (int)  ($props['version'] ?? 0);

    $entries[] = [
        'stringId' => $stringId,
        'numericId' => $numericId,
        'componentBased' => $componentBased,
        'version' => $version,
    ];
}

if (!empty($errors)) {
    foreach ($errors as $e) {
        fwrite(\STDERR, "WARN: $e\n");
    }
}

$count = \count($entries);
echo "Parsed $count item entries.\n";

$now = date('Y-m-d H:i:s');
$inputBase = basename($inputFile);

$listLines = [];
foreach ($entries as $e) {
    $sid = var_export($e['stringId'], true);
    $nid = $e['numericId'];
    $cb = $e['componentBased'] ? 'true' : 'false';
    $ver = $e['version'];
    $listLines[] = "        ['stringId' => $sid, 'numericId' => $nid, 'componentBased' => $cb, 'version' => $ver],";
}

$listBody = implode("\n", $listLines);

$itemTypeListPhp = <<<PHP
    <?php

    declare(strict_types=1);

    namespace watermossmc\mcpe\data;

    /**
     * AUTO-GENERATED — do not edit by hand.
     * Generated: $now
     * Entries: $count
     *
     * Regenerate with:
     *   php generate_item_list.php <input.json> [output_dir]
     */
    final class ItemTypeList
    {
        /**
         * Returns the full item registry as a flat array.
         *
         * Each element has:
         *   - stringId       (string)  Namespaced item ID, e.g. "minecraft:stone"
         *   - numericId      (int)     Network/runtime ID (signed LE short on the wire)
         *   - componentBased (bool)    True when the item is defined via item components
         *   - version        (int)     Item data version
         *
         * @return array<int, array{stringId: string, numericId: int, componentBased: bool, version: int}>
         */
        public static function getEntries(): array
        {
            return [
    $listBody
            ];
        }
    }
    PHP;

$listFile = $outputDir . '/ItemTypeList.php';

file_put_contents($listFile, $itemTypeListPhp);

echo "Written: $listFile\n";
echo "Done.\n";
