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

namespace watermossmc\block\utils;

use watermossmc\data\bedrock\block\BlockLegacyMetadata;
use watermossmc\math\Facing;

final class RailConnectionInfo
{
	public const FLAG_ASCEND = 1 << 24; //used to indicate direction-up

	public const CONNECTIONS = [
		//straights
		BlockLegacyMetadata::RAIL_STRAIGHT_NORTH_SOUTH => [
			Facing::NORTH,
			Facing::SOUTH
		],
		BlockLegacyMetadata::RAIL_STRAIGHT_EAST_WEST => [
			Facing::EAST,
			Facing::WEST
		],

		//ascending
		BlockLegacyMetadata::RAIL_ASCENDING_EAST => [
			Facing::WEST,
			Facing::EAST | self::FLAG_ASCEND
		],
		BlockLegacyMetadata::RAIL_ASCENDING_WEST => [
			Facing::EAST,
			Facing::WEST | self::FLAG_ASCEND
		],
		BlockLegacyMetadata::RAIL_ASCENDING_NORTH => [
			Facing::SOUTH,
			Facing::NORTH | self::FLAG_ASCEND
		],
		BlockLegacyMetadata::RAIL_ASCENDING_SOUTH => [
			Facing::NORTH,
			Facing::SOUTH | self::FLAG_ASCEND
		]
	];

	/* extended meta values for regular rails, to allow curving */
	public const CURVE_CONNECTIONS = [
		BlockLegacyMetadata::RAIL_CURVE_SOUTHEAST => [
			Facing::SOUTH,
			Facing::EAST
		],
		BlockLegacyMetadata::RAIL_CURVE_SOUTHWEST => [
			Facing::SOUTH,
			Facing::WEST
		],
		BlockLegacyMetadata::RAIL_CURVE_NORTHWEST => [
			Facing::NORTH,
			Facing::WEST
		],
		BlockLegacyMetadata::RAIL_CURVE_NORTHEAST => [
			Facing::NORTH,
			Facing::EAST
		]
	];
}
