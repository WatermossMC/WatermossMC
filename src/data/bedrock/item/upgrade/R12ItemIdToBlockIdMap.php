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

namespace watermossmc\data\bedrock\item\upgrade;

use Symfony\Component\Filesystem\Path;
use watermossmc\utils\AssumptionFailedError;
use watermossmc\utils\Filesystem;
use watermossmc\utils\SingletonTrait;
use watermossmc\utils\Utils;

use function is_array;
use function is_string;
use function json_decode;
use function mb_strtolower;

use const JSON_THROW_ON_ERROR;
use const watermossmc\BEDROCK_ITEM_UPGRADE_SCHEMA_PATH;

/**
 * Maps all known 1.12 and lower item IDs to their respective block IDs, where appropriate.
 * If an item ID does not have a corresponding 1.12 block ID, assume the item is not a blockitem.
 *
 * This is only needed for deserializing blockitems from 1.8 and lower (or 1.12 and lower in the case of PM). In 1.9 and
 * above, the blockstate NBT is stored in the itemstack NBT, and the item ID is not used.
 */
final class R12ItemIdToBlockIdMap
{
	use SingletonTrait;

	private static function make() : self
	{
		$map = json_decode(
			Filesystem::fileGetContents(Path::join(BEDROCK_ITEM_UPGRADE_SCHEMA_PATH, '1.12.0_item_id_to_block_id_map.json')),
			associative: true,
			flags: JSON_THROW_ON_ERROR
		);
		if (!is_array($map)) {
			throw new AssumptionFailedError("Invalid blockitem ID mapping table, expected array as root type");
		}

		$builtMap = [];
		foreach (Utils::promoteKeys($map) as $itemId => $blockId) {
			if (!is_string($itemId)) {
				throw new AssumptionFailedError("Invalid blockitem ID mapping table, expected string as key");
			}
			if (!is_string($blockId)) {
				throw new AssumptionFailedError("Invalid blockitem ID mapping table, expected string as value");
			}
			$builtMap[$itemId] = $blockId;
		}

		return new self($builtMap);
	}

	/**
	 * @var string[]
	 * @phpstan-var array<string, string>
	 */
	private array $itemToBlock = [];
	/**
	 * @var string[]
	 * @phpstan-var array<string, string>
	 */
	private array $blockToItem = [];

	/**
	 * @param string[] $itemToBlock
	 * @phpstan-param array<string, string> $itemToBlock
	 */
	public function __construct(array $itemToBlock)
	{
		foreach (Utils::stringifyKeys($itemToBlock) as $itemId => $blockId) {
			$this->itemToBlock[mb_strtolower($itemId, 'US-ASCII')] = $blockId;
			$this->blockToItem[mb_strtolower($blockId, 'US-ASCII')] = $itemId;
		}
	}

	public function itemIdToBlockId(string $itemId) : ?string
	{
		return $this->itemToBlock[mb_strtolower($itemId, 'US-ASCII')] ?? null;
	}

	public function blockIdToItemId(string $blockId) : ?string
	{
		//we don't need this for any functionality, but it might be nice to have for debugging
		return $this->blockToItem[mb_strtolower($blockId, 'US-ASCII')] ?? null;
	}
}
