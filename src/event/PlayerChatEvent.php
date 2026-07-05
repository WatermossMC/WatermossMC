<?php

declare(strict_types=1);

namespace watermossmc\event;

use watermossmc\player\Player;

final class PlayerChatEvent extends Event
{
    public function __construct(
        public readonly Player $player,
        public string $message
    ) {}

    public function getMessage(): string
    {
        return $this->message;
    }

    public function setMessage(string $message): void
    {
        $this->message = $message;
    }
}
