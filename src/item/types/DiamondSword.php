<?php

declare(strict_types=1);

namespace watermossmc\item\types;

use watermossmc\item\Item;
use watermossmc\player\Player;
use watermossmc\util\Logger;

final class DiamondSword extends Item
{
    public function __construct()
    {
        parent::__construct(276, 'Diamond Sword', 'sword');
    }

    public function onUse(Player $player): void
    {
        Logger::info("Player {$player->username} swings a Diamond Sword!");
    }
}
