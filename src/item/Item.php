<?php

<<<<<<< HEAD
=======
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

>>>>>>> 866a1c0 (...)
declare(strict_types=1);

namespace watermossmc\item;

<<<<<<< HEAD
=======
use watermossmc\player\Player;

>>>>>>> 866a1c0 (...)
abstract class Item
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $type = 'generic'
    ) {}

<<<<<<< HEAD
    public function onUse(\watermossmc\player\Player $player): void
=======
    public function onUse(Player $player): void
>>>>>>> 866a1c0 (...)
    {
        // Default: do nothing
    }

<<<<<<< HEAD
    public function onInteract(int $x, int $y, int $z, \watermossmc\player\Player $player): void
=======
    public function onInteract(int $x, int $y, int $z, Player $player): void
>>>>>>> 866a1c0 (...)
    {
        // Default: do nothing
    }
}
