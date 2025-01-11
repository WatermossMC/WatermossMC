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

namespace watermossmc\world\format\io\leveldb;

use watermossmc\VersionInfo;

final class ChunkDataKey
{
	private function __construct()
	{
		//NOOP
	}

	public const HEIGHTMAP_AND_3D_BIOMES = "\x2b";
	public const NEW_VERSION = "\x2c"; //since 1.16.100?
	public const HEIGHTMAP_AND_2D_BIOMES = "\x2d"; //obsolete since 1.18
	public const HEIGHTMAP_AND_2D_BIOME_COLORS = "\x2e"; //obsolete since 1.0
	public const SUBCHUNK = "\x2f";
	public const LEGACY_TERRAIN = "\x30"; //obsolete since 1.0
	public const BLOCK_ENTITIES = "\x31";
	public const ENTITIES = "\x32";
	public const PENDING_SCHEDULED_TICKS = "\x33";
	public const LEGACY_BLOCK_EXTRA_DATA = "\x34"; //obsolete since 1.2.13
	public const BIOME_STATES = "\x35"; //TODO: is this still applicable to 1.18.0?
	public const FINALIZATION = "\x36";
	public const CONVERTER_TAG = "\x37"; //???
	public const BORDER_BLOCKS = "\x38";
	public const HARDCODED_SPAWNERS = "\x39";
	public const PENDING_RANDOM_TICKS = "\x3a";
	public const XXHASH_CHECKSUMS = "\x3b"; //obsolete since 1.18
	public const GENERATION_SEED = "\x3c";
	public const GENERATED_BEFORE_CNC_BLENDING = "\x3d";

	public const OLD_VERSION = "\x76";

	public const PM_DATA_VERSION = VersionInfo::TAG_WORLD_DATA_VERSION;

}
