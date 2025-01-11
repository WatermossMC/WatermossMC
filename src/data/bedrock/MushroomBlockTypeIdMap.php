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

use watermossmc\block\utils\MushroomBlockType;
use watermossmc\data\bedrock\block\BlockLegacyMetadata as LegacyMeta;
use watermossmc\utils\SingletonTrait;

final class MushroomBlockTypeIdMap
{
	use SingletonTrait;
	/** @phpstan-use IntSaveIdMapTrait<MushroomBlockType> */
	use IntSaveIdMapTrait;

	public function __construct()
	{
		foreach (MushroomBlockType::cases() as $case) {
			$this->register(match($case) {
				MushroomBlockType::PORES => LegacyMeta::MUSHROOM_BLOCK_ALL_PORES,
				MushroomBlockType::CAP_NORTHWEST => LegacyMeta::MUSHROOM_BLOCK_CAP_NORTHWEST_CORNER,
				MushroomBlockType::CAP_NORTH => LegacyMeta::MUSHROOM_BLOCK_CAP_NORTH_SIDE,
				MushroomBlockType::CAP_NORTHEAST => LegacyMeta::MUSHROOM_BLOCK_CAP_NORTHEAST_CORNER,
				MushroomBlockType::CAP_WEST => LegacyMeta::MUSHROOM_BLOCK_CAP_WEST_SIDE,
				MushroomBlockType::CAP_MIDDLE => LegacyMeta::MUSHROOM_BLOCK_CAP_TOP_ONLY,
				MushroomBlockType::CAP_EAST => LegacyMeta::MUSHROOM_BLOCK_CAP_EAST_SIDE,
				MushroomBlockType::CAP_SOUTHWEST => LegacyMeta::MUSHROOM_BLOCK_CAP_SOUTHWEST_CORNER,
				MushroomBlockType::CAP_SOUTH => LegacyMeta::MUSHROOM_BLOCK_CAP_SOUTH_SIDE,
				MushroomBlockType::CAP_SOUTHEAST => LegacyMeta::MUSHROOM_BLOCK_CAP_SOUTHEAST_CORNER,
				MushroomBlockType::ALL_CAP => LegacyMeta::MUSHROOM_BLOCK_ALL_CAP,
			}, $case);
		}
	}
}
