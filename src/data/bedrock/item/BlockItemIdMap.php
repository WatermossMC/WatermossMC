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

namespace watermossmc\data\bedrock\item;

use watermossmc\data\bedrock\BedrockDataFiles;
use watermossmc\utils\AssumptionFailedError;
use watermossmc\utils\Filesystem;
use watermossmc\utils\SingletonTrait;

use function array_flip;
use function is_array;
use function json_decode;

use const JSON_THROW_ON_ERROR;

/**
 * Bidirectional map of block IDs to their corresponding blockitem IDs, used for storing items on disk
 */
final class BlockItemIdMap
{
	use SingletonTrait;

	private static function make() : self
	{
		$map = json_decode(
			Filesystem::fileGetContents(BedrockDataFiles::BLOCK_ID_TO_ITEM_ID_MAP_JSON),
			associative: true,
			flags: JSON_THROW_ON_ERROR
		);
		if (!is_array($map)) {
			throw new AssumptionFailedError("Invalid blockitem ID mapping table, expected array as root type");
		}

		return new self($map);
	}

	/**
	 * @var string[]
	 * @phpstan-var array<string, string>
	 */
	private array $itemToBlockId;

	/**
	 * @param string[] $blockToItemId
	 * @phpstan-param array<string, string> $blockToItemId
	 */
	public function __construct(private array $blockToItemId)
	{
		$this->itemToBlockId = array_flip($this->blockToItemId);
	}

	public function lookupItemId(string $blockId) : ?string
	{
		return $this->blockToItemId[$blockId] ?? null;
	}

	public function lookupBlockId(string $itemId) : ?string
	{
		return $this->itemToBlockId[$itemId] ?? null;
	}
}
