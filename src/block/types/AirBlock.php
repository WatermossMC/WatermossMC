<?php

declare(strict_types=1);

namespace watermossmc\block\types;

use watermossmc\block\Block;
use watermossmc\player\Player;
use watermossmc\util\Logger;

final class AirBlock extends Block
{
    public function __construct()
    {
        parent::__construct(0, 'Air');
    }

    public function onInteract(int $x, int $y, int $z, Player $player): void
    {
        Logger::debug("Player {$player->username} interacted with Air at $x, $y, $z");
    }
}
