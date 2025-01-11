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

namespace watermossmc\data\bedrock;

use watermossmc\utils\AssumptionFailedError;
use watermossmc\utils\Filesystem;
use watermossmc\utils\SingletonTrait;
use watermossmc\utils\Utils;

use function array_keys;
use function gettype;
use function is_array;
use function is_string;
use function json_decode;

use const JSON_THROW_ON_ERROR;

/**
 * Tracks Minecraft Bedrock item tags, and the item IDs which belong to them
 *
 * @internal
 */
final class ItemTagToIdMap
{
	use SingletonTrait;

	private static function make() : self
	{
		$map = json_decode(Filesystem::fileGetContents(BedrockDataFiles::ITEM_TAGS_JSON), true, flags: JSON_THROW_ON_ERROR);
		if (!is_array($map)) {
			throw new AssumptionFailedError("Invalid item tag map, expected array");
		}
		$cleanMap = [];
		foreach (Utils::promoteKeys($map) as $tagName => $ids) {
			if (!is_string($tagName)) {
				throw new AssumptionFailedError("Invalid item tag name $tagName, expected string as key");
			}
			if (!is_array($ids)) {
				throw new AssumptionFailedError("Invalid item tag $tagName, expected array of IDs as value");
			}
			$cleanIds = [];
			foreach ($ids as $id) {
				if (!is_string($id)) {
					throw new AssumptionFailedError("Invalid item tag $tagName, expected string as ID, got " . gettype($id));
				}
				$cleanIds[] = $id;
			}
			$cleanMap[$tagName] = $cleanIds;
		}

		return new self($cleanMap);
	}

	/**
	 * @var true[][]
	 * @phpstan-var array<string, array<string, true>>
	 */
	private array $tagToIdsMap = [];

	/**
	 * @param string[][] $tagToIds
	 * @phpstan-param array<string, list<string>> $tagToIds
	 */
	public function __construct(
		array $tagToIds
	) {
		foreach (Utils::stringifyKeys($tagToIds) as $tag => $ids) {
			foreach ($ids as $id) {
				$this->tagToIdsMap[$tag][$id] = true;
			}
		}
	}

	/**
	 * @return string[]
	 * @phpstan-return list<string>
	 */
	public function getIdsForTag(string $tag) : array
	{
		return array_keys($this->tagToIdsMap[$tag] ?? []);
	}

	public function tagContainsId(string $tag, string $id) : bool
	{
		return isset($this->tagToIdsMap[$tag][$id]);
	}

	public function addIdToTag(string $tag, string $id) : void
	{
		$this->tagToIdsMap[$tag][$id] = true;
	}
}
