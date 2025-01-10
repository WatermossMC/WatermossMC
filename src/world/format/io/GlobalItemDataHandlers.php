<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.watermossmc.net/
 *
 *
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
