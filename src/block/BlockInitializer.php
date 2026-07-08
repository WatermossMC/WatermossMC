<?php

/*
 * __        __    _                                    __  __  ____
 * \ \      / /_ _| |_ ___ _ __ _ __ ___   ___  ___ ___|  \/  |/ ___|
 *  \ \ /\ / / _` | __/ _ \ '__| '_ ` _ \ / _ \/ __/ __| |\/| | |
 *   \ V  V / (_| | ||  __/ |  | | | | | | (_) \__ \__ \ |  | | |___
 *    \_/\_/ \__,_|\__\___|_|  |_| |_| |_|\___/|___/___/_|  |_|\____|
 *
 * WatermossMC
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author WatermossMC Team
 * @link https://github.com/watermossmc/WatermossMC
 */

declare (strict_types=1);

namespace watermossmc\block;

use watermossmc\block\types\AirBlock;
use watermossmc\block\types\BedrockBlock;
use watermossmc\block\types\DirtBlock;
use watermossmc\block\types\GrassBlock;
use watermossmc\block\types\StoneBlock;

final class BlockInitializer
{
    public static function init(): void
    {
        BlockRegistry::register(new AirBlock());
        BlockRegistry::register(new StoneBlock());
        BlockRegistry::register(new GrassBlock());
        BlockRegistry::register(new DirtBlock());
        BlockRegistry::register(new BedrockBlock());
    }
}
