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
