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

use const watermossmc\BEDROCK_DATA_PATH;

final class BedrockDataFiles
{
	private function __construct()
	{
		//NOOP
	}

	public const BANNER_PATTERNS_JSON = BEDROCK_DATA_PATH . '/banner_patterns.json';
	public const BIOME_DEFINITIONS_NBT = BEDROCK_DATA_PATH . '/biome_definitions.nbt';
	public const BIOME_DEFINITIONS_FULL_NBT = BEDROCK_DATA_PATH . '/biome_definitions_full.nbt';
	public const BIOME_ID_MAP_JSON = BEDROCK_DATA_PATH . '/biome_id_map.json';
	public const BLOCK_ID_TO_ITEM_ID_MAP_JSON = BEDROCK_DATA_PATH . '/block_id_to_item_id_map.json';
	public const BLOCK_PROPERTIES_TABLE_JSON = BEDROCK_DATA_PATH . '/block_properties_table.json';
	public const BLOCK_STATE_META_MAP_JSON = BEDROCK_DATA_PATH . '/block_state_meta_map.json';
	public const CANONICAL_BLOCK_STATES_NBT = BEDROCK_DATA_PATH . '/canonical_block_states.nbt';
	public const COMMAND_ARG_TYPES_JSON = BEDROCK_DATA_PATH . '/command_arg_types.json';
	public const CREATIVEITEMS_JSON = BEDROCK_DATA_PATH . '/creativeitems.json';
	public const ENTITY_ID_MAP_JSON = BEDROCK_DATA_PATH . '/entity_id_map.json';
	public const ENTITY_IDENTIFIERS_NBT = BEDROCK_DATA_PATH . '/entity_identifiers.nbt';
	public const ITEM_TAGS_JSON = BEDROCK_DATA_PATH . '/item_tags.json';
	public const LEVEL_SOUND_ID_MAP_JSON = BEDROCK_DATA_PATH . '/level_sound_id_map.json';
	public const PARTICLE_ID_MAP_JSON = BEDROCK_DATA_PATH . '/particle_id_map.json';
	public const PROTOCOL_INFO_JSON = BEDROCK_DATA_PATH . '/protocol_info.json';
	public const R12_TO_CURRENT_BLOCK_MAP_BIN = BEDROCK_DATA_PATH . '/r12_to_current_block_map.bin';
	public const R16_TO_CURRENT_ITEM_MAP_JSON = BEDROCK_DATA_PATH . '/r16_to_current_item_map.json';
	public const REQUIRED_ITEM_LIST_JSON = BEDROCK_DATA_PATH . '/required_item_list.json';
}
