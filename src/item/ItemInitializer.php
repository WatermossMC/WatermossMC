<?php

declare(strict_types=1);

namespace watermossmc\item;

use watermossmc\item\types\Apple;
use watermossmc\item\types\CobblestoneItem;
use watermossmc\item\types\DiamondSword;

final class ItemInitializer
{
    public static function init(): void
    {
        ItemRegistry::register(new DiamondSword());
        ItemRegistry::register(new Apple());
        ItemRegistry::register(new CobblestoneItem());
        // Add more items here
    }
}
