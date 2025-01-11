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
