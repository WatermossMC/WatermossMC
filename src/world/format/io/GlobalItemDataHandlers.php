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
use watermossmc\data\bedrock\item\BlockItemIdMap;
use watermossmc\data\bedrock\item\ItemDeserializer;
use watermossmc\data\bedrock\item\ItemSerializer;
use watermossmc\data\bedrock\item\upgrade\ItemDataUpgrader;
use watermossmc\data\bedrock\item\upgrade\ItemIdMetaUpgrader;
use watermossmc\data\bedrock\item\upgrade\ItemIdMetaUpgradeSchemaUtils;
use watermossmc\data\bedrock\item\upgrade\LegacyItemIdToStringIdMap;
use watermossmc\data\bedrock\item\upgrade\R12ItemIdToBlockIdMap;
use watermossmc\network\mcpe\convert\TypeConverter;

use const PHP_INT_MAX;
use const watermossmc\BEDROCK_ITEM_UPGRADE_SCHEMA_PATH;

final class GlobalItemDataHandlers
{
	private static ?ItemSerializer $itemSerializer = null;

	private static ?ItemDeserializer $itemDeserializer = null;

	private static ?ItemDataUpgrader $itemDataUpgrader = null;

	public static function getSerializer() : ItemSerializer
	{
		return self::$itemSerializer ??= new ItemSerializer(GlobalBlockStateHandlers::getSerializer());
	}

	public static function getDeserializer() : ItemDeserializer
	{
		return self::$itemDeserializer ??= new ItemDeserializer(GlobalBlockStateHandlers::getDeserializer());
	}

	public static function getUpgrader() : ItemDataUpgrader
	{
		return self::$itemDataUpgrader ??= new ItemDataUpgrader(
			new ItemIdMetaUpgrader(ItemIdMetaUpgradeSchemaUtils::loadSchemas(Path::join(BEDROCK_ITEM_UPGRADE_SCHEMA_PATH, 'id_meta_upgrade_schema'), PHP_INT_MAX)),
			LegacyItemIdToStringIdMap::getInstance(),
			R12ItemIdToBlockIdMap::getInstance(),
			GlobalBlockStateHandlers::getUpgrader(),
			BlockItemIdMap::getInstance(),
			TypeConverter::getInstance()->getBlockTranslator()->getBlockStateDictionary()
		);
	}
}
