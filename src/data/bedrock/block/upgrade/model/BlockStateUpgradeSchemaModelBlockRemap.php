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

final class BlockStateUpgradeSchemaModelBlockRemap
{
	/**
	 * @var BlockStateUpgradeSchemaModelTag[]|null
	 * @phpstan-var array<string, BlockStateUpgradeSchemaModelTag>|null
	 * @required
	 */
	public ?array $oldState;

	/**
	 * Either this or newFlattenedName must be present
	 * Due to technical limitations of jsonmapper, we can't use a union type here
	 */
	public string $newName;
	/**
	 * Either this or newName must be present
	 * Due to technical limitations of jsonmapper, we can't use a union type here
	 */
	public BlockStateUpgradeSchemaModelFlattenInfo $newFlattenedName;

	/**
	 * @var BlockStateUpgradeSchemaModelTag[]|null
	 * @phpstan-var array<string, BlockStateUpgradeSchemaModelTag>|null
	 * @required
	 */
	public ?array $newState;

	/**
	 * @var string[]
	 * @phpstan-var list<string>
	 * May not be present in older schemas
	 */
	public array $copiedState;

	/**
	 * @param BlockStateUpgradeSchemaModelTag[] $oldState
	 * @param BlockStateUpgradeSchemaModelTag[] $newState
	 * @param string[]                          $copiedState
	 * @phpstan-param array<string, BlockStateUpgradeSchemaModelTag> $oldState
	 * @phpstan-param array<string, BlockStateUpgradeSchemaModelTag> $newState
	 * @phpstan-param list<string> $copiedState
	 */
	public function __construct(array $oldState, string|BlockStateUpgradeSchemaModelFlattenInfo $newNameRule, array $newState, array $copiedState)
	{
		$this->oldState = count($oldState) === 0 ? null : $oldState;
		if ($newNameRule instanceof BlockStateUpgradeSchemaModelFlattenInfo) {
			$this->newFlattenedName = $newNameRule;
		} else {
			$this->newName = $newNameRule;
		}
		$this->newState = count($newState) === 0 ? null : $newState;
		$this->copiedState = $copiedState;
	}
}
