<?php

declare(strict_types=1);

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
