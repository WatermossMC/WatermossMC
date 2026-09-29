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

namespace watermossmc\block\types;

use watermossmc\block\Block;
use watermossmc\network\mcpe\protocol\types\WindowTypes;

final class CraftingTableBlock extends Block
{
    public function __construct()
    {
        parent::__construct(Block::CRAFTING_TABLE, 'minecraft:crafting_table');
    }

    public function getHardness(): float
    {
        return 2.5;
    }

    public function getContainerWindowType(): int
    {
        return WindowTypes::WORKBENCH;
    }
}
