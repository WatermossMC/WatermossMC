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

/*
 * This file is part of the WatermossMC
 * (c) 2025 WatermossMC <gameplaytebakgambard>
 *
 * @License Apache 2.0
 */

namespace phpunit\data\bedrock\block;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Path;
use watermossmc\data\bedrock\block\BlockStateData;
use watermossmc\data\bedrock\block\upgrade\BlockStateUpgradeSchemaUtils;

use function sprintf;

use const PHP_INT_MAX;
use const watermossmc\BEDROCK_BLOCK_UPGRADE_SCHEMA_PATH;

final class BlockStateDataTest extends TestCase
{
	public function testCurrentVersion() : void
	{
		foreach (BlockStateUpgradeSchemaUtils::loadSchemas(
			Path::join(BEDROCK_BLOCK_UPGRADE_SCHEMA_PATH, 'nbt_upgrade_schema'),
			PHP_INT_MAX
		) as $schema) {
			$expected = BlockStateData::CURRENT_VERSION;
			$actual = $schema->getVersionId();
			self::assertLessThanOrEqual($expected, $actual, sprintf(
				"Schema version %d (%d.%d.%d.%d) is newer than the current version %d (%d.%d.%d.%d)",
				$actual,
				($actual >> 24) & 0xff,
				($actual >> 16) & 0xff,
				($actual >> 8) & 0xff,
				$actual & 0xff,
				$expected,
				($expected >> 24) & 0xff,
				($expected >> 16) & 0xff,
				($expected >> 8) & 0xff,
				$expected & 0xff
			));
		}
	}
}
