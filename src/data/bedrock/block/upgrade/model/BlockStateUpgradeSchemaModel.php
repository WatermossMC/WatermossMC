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

namespace watermossmc\data\bedrock\block\upgrade\model;

use function count;
use function is_array;

/**
 * Model for loading upgrade schema data from JSON.
 */
final class BlockStateUpgradeSchemaModel implements \JsonSerializable
{
	/** @required */
	public int $maxVersionMajor;
	/** @required */
	public int $maxVersionMinor;
	/** @required */
	public int $maxVersionPatch;
	/** @required */
	public int $maxVersionRevision;

	/**
	 * @var string[]
	 * @phpstan-var array<string, string>
	 */
	public array $renamedIds;

	/**
	 * @var BlockStateUpgradeSchemaModelTag[][]
	 * @phpstan-var array<string, array<string, BlockStateUpgradeSchemaModelTag>>
	 */
	public array $addedProperties;

	/**
	 * @var string[][]
	 * @phpstan-var array<string, list<string>>
	 */
	public array $removedProperties;

	/**
	 * @var string[][]
	 * @phpstan-var array<string, array<string, string>>
	 */
	public array $renamedProperties;

	/**
	 * @var string[][]
	 * @phpstan-var array<string, array<string, string>>
	 */
	public array $remappedPropertyValues;

	/**
	 * @var BlockStateUpgradeSchemaModelValueRemap[][]
	 * @phpstan-var array<string, list<BlockStateUpgradeSchemaModelValueRemap>>
	 */
	public array $remappedPropertyValuesIndex;

	/**
	 * @var BlockStateUpgradeSchemaModelFlattenInfo[]
	 * @phpstan-var array<string, BlockStateUpgradeSchemaModelFlattenInfo>
	 */
	public array $flattenedProperties;

	/**
	 * @var BlockStateUpgradeSchemaModelBlockRemap[][]
	 * @phpstan-var array<string, list<BlockStateUpgradeSchemaModelBlockRemap>>
	 */
	public array $remappedStates;

	/**
	 * @return mixed[]
	 */
	public function jsonSerialize() : array
	{
		$result = (array) $this;

		foreach ($result as $k => $v) {
			if (is_array($v) && count($v) === 0) {
				unset($result[$k]);
			}
		}

		return $result;
	}
}
