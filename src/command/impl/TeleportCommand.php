<?php

declare(strict_types=1);

namespace watermossmc\command\impl;

use watermossmc\command\Command;
use watermossmc\player\Player;

final class TeleportCommand extends Command
{
    public function __construct()
    {
        parent::__construct(
            'tp',
            'Teleports a player',
            '/tp <player>'
        );
    }

    public function execute(mixed $sender, array $args): void
    {
        if (\count($args) < 1) {
            $this->sendMessage($sender, "Usage: /tp <player>");
            return;
        }

        $targetName = $args[0];
        $target = \watermossmc\player\PlayerManager::getByName($targetName);

        if ($target === null) {
            $this->sendMessage($sender, "Player not found.");
            return;
        }

        if ($sender instanceof Player) {
            $sender->teleport(
                $target->x,
                $target->y,
                $target->z,
                $target->yaw,
                $target->pitch
            );
            $this->sendMessage($sender, "Teleported to {$target->getName()}.");
            $this->sendMessage($target, "You were teleported by " . $sender->getName() . ".");
        } else {
            $this->sendMessage($sender, "Console cannot teleport. Use /tp <player> <x> <y> <z>");
        }
    }
}
