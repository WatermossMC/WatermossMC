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

namespace watermossmc\network\mcpe\convert;

use watermossmc\network\mcpe\protocol\serializer\ItemTypeDictionary;
use watermossmc\network\mcpe\protocol\types\ItemTypeEntry;
use watermossmc\utils\AssumptionFailedError;
use watermossmc\utils\Utils;

use function is_array;
use function is_bool;
use function is_int;
use function is_string;
use function json_decode;

final class ItemTypeDictionaryFromDataHelper
{
	public static function loadFromString(string $data) : ItemTypeDictionary
	{
		$table = json_decode($data, true);
		if (!is_array($table)) {
			throw new AssumptionFailedError("Invalid item list format");
		}

		$params = [];
		foreach (Utils::promoteKeys($table) as $name => $entry) {
			if (!is_array($entry) || !is_string($name) || !isset($entry["component_based"], $entry["runtime_id"]) || !is_bool($entry["component_based"]) || !is_int($entry["runtime_id"])) {
				throw new AssumptionFailedError("Invalid item list format");
			}
			$params[] = new ItemTypeEntry($name, $entry["runtime_id"], $entry["component_based"]);
		}
		return new ItemTypeDictionary($params);
	}
}
