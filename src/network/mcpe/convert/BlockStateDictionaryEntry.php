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

use watermossmc\data\bedrock\block\BlockStateData;
use watermossmc\nbt\LittleEndianNbtSerializer;
use watermossmc\nbt\tag\CompoundTag;
use watermossmc\nbt\tag\Tag;
use watermossmc\nbt\TreeRoot;
use watermossmc\utils\Utils;

use function count;
use function ksort;

use const SORT_STRING;

final class BlockStateDictionaryEntry
{
	/**
	 * @var string[]
	 * @phpstan-var array<string, string>
	 */
	private static array $uniqueRawStates = [];

	private string $rawStateProperties;

	/**
	 * @param Tag[] $stateProperties
	 * @phpstan-param array<string, Tag> $stateProperties
	 */
	public function __construct(
		private string $stateName,
		array $stateProperties,
		private int $meta
	) {
		$rawStateProperties = self::encodeStateProperties($stateProperties);
		$this->rawStateProperties = self::$uniqueRawStates[$rawStateProperties] ??= $rawStateProperties;
	}

	public function getStateName() : string
	{
		return $this->stateName;
	}

	public function getRawStateProperties() : string
	{
		return $this->rawStateProperties;
	}

	public function generateStateData() : BlockStateData
	{
		return new BlockStateData(
			$this->stateName,
			self::decodeStateProperties($this->rawStateProperties),
			BlockStateData::CURRENT_VERSION
		);
	}

	public function getMeta() : int
	{
		return $this->meta;
	}

	/**
	 * @return Tag[]
	 */
	public static function decodeStateProperties(string $rawProperties) : array
	{
		if ($rawProperties === "") {
			return [];
		}
		return (new LittleEndianNbtSerializer())->read($rawProperties)->mustGetCompoundTag()->getValue();
	}

	/**
	 * @param Tag[] $properties
	 * @phpstan-param array<string, Tag> $properties
	 */
	public static function encodeStateProperties(array $properties) : string
	{
		if (count($properties) === 0) {
			return "";
		}
		//TODO: make a more efficient encoding - NBT will do for now, but it's not very compact
		ksort($properties, SORT_STRING);
		$tag = new CompoundTag();
		foreach (Utils::stringifyKeys($properties) as $k => $v) {
			$tag->setTag($k, $v);
		}
		return (new LittleEndianNbtSerializer())->write(new TreeRoot($tag));
	}
}
