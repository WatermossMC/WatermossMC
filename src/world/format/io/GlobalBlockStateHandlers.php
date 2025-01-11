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

namespace watermossmc\world\format\io;

use Symfony\Component\Filesystem\Path;
use watermossmc\data\bedrock\block\BlockStateData;
use watermossmc\data\bedrock\block\BlockTypeNames;
use watermossmc\data\bedrock\block\convert\BlockObjectToStateSerializer;
use watermossmc\data\bedrock\block\convert\BlockStateToObjectDeserializer;
use watermossmc\data\bedrock\block\upgrade\BlockDataUpgrader;
use watermossmc\data\bedrock\block\upgrade\BlockIdMetaUpgrader;
use watermossmc\data\bedrock\block\upgrade\BlockStateUpgrader;
use watermossmc\data\bedrock\block\upgrade\BlockStateUpgradeSchemaUtils;
use watermossmc\data\bedrock\block\upgrade\LegacyBlockIdToStringIdMap;
use watermossmc\utils\Filesystem;

use const PHP_INT_MAX;
use const watermossmc\BEDROCK_BLOCK_UPGRADE_SCHEMA_PATH;

/**
 * Provides global access to blockstate serializers for all world providers.
 * TODO: Get rid of this. This is necessary to enable plugins to register custom serialize/deserialize handlers, and
 * also because we can't break BC of WorldProvider before PM5. While this is a sucky hack, it provides meaningful
 * benefits for now.
 */
final class GlobalBlockStateHandlers
{
	private static ?BlockObjectToStateSerializer $blockStateSerializer = null;

	private static ?BlockStateToObjectDeserializer $blockStateDeserializer = null;

	private static ?BlockDataUpgrader $blockDataUpgrader = null;

	private static ?BlockStateData $unknownBlockStateData = null;

	public static function getDeserializer() : BlockStateToObjectDeserializer
	{
		return self::$blockStateDeserializer ??= new BlockStateToObjectDeserializer();
	}

	public static function getSerializer() : BlockObjectToStateSerializer
	{
		return self::$blockStateSerializer ??= new BlockObjectToStateSerializer();
	}

	public static function getUpgrader() : BlockDataUpgrader
	{
		if (self::$blockDataUpgrader === null) {
			$blockStateUpgrader = new BlockStateUpgrader(BlockStateUpgradeSchemaUtils::loadSchemas(
				Path::join(BEDROCK_BLOCK_UPGRADE_SCHEMA_PATH, 'nbt_upgrade_schema'),
				PHP_INT_MAX
			));
			self::$blockDataUpgrader = new BlockDataUpgrader(
				BlockIdMetaUpgrader::loadFromString(
					Filesystem::fileGetContents(Path::join(
						BEDROCK_BLOCK_UPGRADE_SCHEMA_PATH,
						'id_meta_to_nbt/1.12.0.bin'
					)),
					LegacyBlockIdToStringIdMap::getInstance(),
					$blockStateUpgrader
				),
				$blockStateUpgrader
			);
		}

		return self::$blockDataUpgrader;
	}

	public static function getUnknownBlockStateData() : BlockStateData
	{
		return self::$unknownBlockStateData ??= BlockStateData::current(BlockTypeNames::INFO_UPDATE, []);
	}
}
