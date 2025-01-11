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

namespace watermossmc\data\bedrock\block\upgrade;

use watermossmc\nbt\tag\Tag;
use watermossmc\utils\Utils;

use function array_diff;
use function count;

final class BlockStateUpgradeSchemaBlockRemap
{
	/**
	 * @param Tag[]    $oldState
	 * @param Tag[]    $newState
	 * @param string[] $copiedState
	 *
	 * @phpstan-param array<string, Tag> $oldState
	 * @phpstan-param array<string, Tag> $newState
	 * @phpstan-param list<string>       $copiedState
	 */
	public function __construct(
		public array $oldState,
		public string|BlockStateUpgradeSchemaFlattenInfo $newName,
		public array $newState,
		public array $copiedState
	) {
	}

	public function equals(self $that) : bool
	{
		$sameName = $this->newName === $that->newName ||
			(
				$this->newName instanceof BlockStateUpgradeSchemaFlattenInfo &&
				$that->newName instanceof BlockStateUpgradeSchemaFlattenInfo &&
				$this->newName->equals($that->newName)
			);
		if (!$sameName) {
			return false;
		}

		if (
			count($this->oldState) !== count($that->oldState) ||
			count($this->newState) !== count($that->newState) ||
			count($this->copiedState) !== count($that->copiedState) ||
			count(array_diff($this->copiedState, $that->copiedState)) !== 0
		) {
			return false;
		}
		foreach (Utils::stringifyKeys($this->oldState) as $propertyName => $propertyValue) {
			if (!isset($that->oldState[$propertyName]) || !$that->oldState[$propertyName]->equals($propertyValue)) {
				//different filter value
				return false;
			}
		}
		foreach (Utils::stringifyKeys($this->newState) as $propertyName => $propertyValue) {
			if (!isset($that->newState[$propertyName]) || !$that->newState[$propertyName]->equals($propertyValue)) {
				//different replacement value
				return false;
			}
		}

		return true;
	}
}
