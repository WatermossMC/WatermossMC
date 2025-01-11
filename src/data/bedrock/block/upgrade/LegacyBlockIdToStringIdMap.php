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

namespace watermossmc\data\bedrock\block\upgrade;

use Symfony\Component\Filesystem\Path;
use watermossmc\data\bedrock\LegacyToStringIdMap;
use watermossmc\utils\SingletonTrait;

final class LegacyBlockIdToStringIdMap extends LegacyToStringIdMap
{
	use SingletonTrait;

	public function __construct()
	{
		parent::__construct(Path::join(\watermossmc\BEDROCK_BLOCK_UPGRADE_SCHEMA_PATH, 'block_legacy_id_map.json'));
	}
}
