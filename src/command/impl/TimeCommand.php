<?php

declare(strict_types=1);

namespace watermossmc\command\impl;

use watermossmc\command\Command;
use watermossmc\Server;
use watermossmc\util\Permission;

final class TimeCommand extends Command
{
    private const TIME_SPECS = ['day' => 1000, 'sunrise' => 23000, 'noon' => 6000, 'sunset' => 12000, 'night' => 13000, 'midnight' => 18000];

    public function __construct()
    {
        parent::__construct('time', 'Changes or queries the world time', '/time <set|add|query> <value>', Permission::ROLE_OPERATOR);
    }

    public function execute(\watermossmc\command\CommandSender $sender, array $args): void
    {
        $world = Server::getInstance()?->getWorld();
        if ($world === null || $args === []) {
            $this->sendMessage($sender, "Usage: {$this->usage}");
            return;
        }
        $action = strtolower($args[0]);
        if ($action === 'query') {
            $query = strtolower($args[1] ?? 'daytime');
            $value = match ($query) {
                'daytime' => $world->getDayTime(),
                'gametime' => $world->getTime(),
                'day' => intdiv($world->getTime(), 24000),
                default => null,
            };
            if ($value === null) {
                $this->sendMessage($sender, 'Unknown time query. Expected daytime, gametime, or day.');
                return;
            }
            $this->sendMessage($sender, 'The time is ' . $value . '.');
            return;
        }
        $value = $args[1] ?? null;
        $amount = $value !== null ? self::TIME_SPECS[strtolower($value)] ?? (is_numeric($value) ? (int) $value : null) : null;
        if ($amount === null || !in_array($action, ['set', 'add'], true)) {
            $this->sendMessage($sender, "Usage: {$this->usage}");
            return;
        }
        if ($action === 'set') {
            $world->setDayTime($amount);
            $this->sendMessage($sender, 'Set the time to ' . $amount . '.');
            return;
        }
        $world->setTime($world->getTime() + $amount);
        $this->sendMessage($sender, 'Added ' . $amount . ' to the time.');
    }
}
