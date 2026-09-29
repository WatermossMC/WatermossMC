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

declare(strict_types=1);

namespace watermossmc\item;

use watermossmc\item\types\Apple;
use watermossmc\item\types\Coal;
use watermossmc\item\types\CobblestoneItem;
use watermossmc\item\types\CobblestoneSlab;
use watermossmc\item\types\CobblestoneWall;
use watermossmc\item\types\CraftingTableItem;
use watermossmc\item\types\Diamond;
use watermossmc\item\types\DiamondBlockItem;
use watermossmc\item\types\DiamondOreItem;
use watermossmc\item\types\DiamondSword;
use watermossmc\item\types\Ladder;
use watermossmc\item\types\Lever;
use watermossmc\item\types\Stick;
use watermossmc\item\types\StoneStairs;
use watermossmc\item\types\Torch;

final class ItemInitializer
{
    public static function init(): void
    {
        ItemRegistry::register(new DiamondSword());
        ItemRegistry::register(new Apple());
        ItemRegistry::register(new CobblestoneItem());
        ItemRegistry::register(new Stick());
        ItemRegistry::register(new Coal());
        ItemRegistry::register(new CobblestoneWall());
        ItemRegistry::register(new Ladder());
        ItemRegistry::register(new Lever());
        ItemRegistry::register(new CobblestoneSlab());
        ItemRegistry::register(new StoneStairs());
        ItemRegistry::register(new Torch());
        ItemRegistry::register(new Diamond());
        ItemRegistry::register(new DiamondOreItem());
        ItemRegistry::register(new DiamondBlockItem());
        ItemRegistry::register(new CraftingTableItem());
    }
}
