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

namespace watermossmc;

use function define;
use function defined;
use function dirname;

// composer autoload doesn't use require_once and also pthreads can inherit things
if (defined('watermossmc\_CORE_CONSTANTS_INCLUDED')) {
	return;
}
define('watermossmc\_CORE_CONSTANTS_INCLUDED', true);

define('watermossmc\PATH', dirname(__DIR__) . '/');
define('watermossmc\RESOURCE_PATH', dirname(__DIR__) . '/resources/');
define('watermossmc\BEDROCK_DATA_PATH', dirname(__DIR__) . '/vendor/watermossmc/bedrock-data/');
define('watermossmc\LOCALE_DATA_PATH', dirname(__DIR__) . '/vendor/watermossmc/locale-data/');
define('watermossmc\BEDROCK_BLOCK_UPGRADE_SCHEMA_PATH', dirname(__DIR__) . '/vendor/watermossmc/bedrock-block-upgrade-schema/');
define('watermossmc\BEDROCK_ITEM_UPGRADE_SCHEMA_PATH', dirname(__DIR__) . '/vendor/watermossmc/bedrock-item-upgrade-schema/');
define('watermossmc\COMPOSER_AUTOLOADER_PATH', dirname(__DIR__) . '/vendor/autoload.php');
