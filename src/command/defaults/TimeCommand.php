<?php

/*
 *
 * This file part of WatermossMC.
 *
 *  __        __    _                                    __  __  ____
 *  \ \      / /_ _| |_ ___ _ __ _ __ ___   ___  ___ ___|  \/  |/ ___|
 *   \ \ /\ / / _` | __/ _ \ '__| '_ ` _ \ / _ \/ __/ __| |\/| | |
 *    \ V  V / (_| | ||  __/ |  | | | | | | (_) \__ \__ \ |  | | |___
 *     \_/\_/ \__,_|\__\___|_|  |_| |_| |_|\___/|___/___/_|  |_|\____|
 *
 * @author WatermossMC Team
 * @license Apache 2.0
 */

declare(strict_types=1);

namespace watermossmc\command\defaults;

use watermossmc\command\Command;
use watermossmc\command\CommandSender;
use watermossmc\command\utils\InvalidCommandSyntaxException;
use watermossmc\lang\KnownTranslationFactory;
use watermossmc\permission\DefaultPermissionNames;
use watermossmc\player\Player;
use watermossmc\world\World;

use function count;

class TimeCommand extends VanillaCommand
{
	public function __construct()
	{
		parent::__construct(
			"time",
			KnownTranslationFactory::watermossmc_command_time_description(),
			KnownTranslationFactory::watermossmc_command_time_usage()
		);
		$this->setPermissions([
			DefaultPermissionNames::COMMAND_TIME_ADD,
			DefaultPermissionNames::COMMAND_TIME_SET,
			DefaultPermissionNames::COMMAND_TIME_START,
			DefaultPermissionNames::COMMAND_TIME_STOP,
			DefaultPermissionNames::COMMAND_TIME_QUERY
		]);
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args)
	{
		if (count($args) < 1) {
			throw new InvalidCommandSyntaxException();
		}

		if ($args[0] === "start") {
			if (!$this->testPermission($sender, DefaultPermissionNames::COMMAND_TIME_START)) {
				return true;
			}
			foreach ($sender->getServer()->getWorldManager()->getWorlds() as $world) {
				$world->startTime();
			}
			Command::broadcastCommandMessage($sender, "Restarted the time");
			return true;
		} elseif ($args[0] === "stop") {
			if (!$this->testPermission($sender, DefaultPermissionNames::COMMAND_TIME_STOP)) {
				return true;
			}
			foreach ($sender->getServer()->getWorldManager()->getWorlds() as $world) {
				$world->stopTime();
			}
			Command::broadcastCommandMessage($sender, "Stopped the time");
			return true;
		} elseif ($args[0] === "query") {
			if (!$this->testPermission($sender, DefaultPermissionNames::COMMAND_TIME_QUERY)) {
				return true;
			}
			if ($sender instanceof Player) {
				$world = $sender->getWorld();
			} else {
				$world = $sender->getServer()->getWorldManager()->getDefaultWorld();
			}
			$sender->sendMessage($sender->getLanguage()->translate(KnownTranslationFactory::commands_time_query((string) $world->getTime())));
			return true;
		}

		if (count($args) < 2) {
			throw new InvalidCommandSyntaxException();
		}

		if ($args[0] === "set") {
			if (!$this->testPermission($sender, DefaultPermissionNames::COMMAND_TIME_SET)) {
				return true;
			}

			switch ($args[1]) {
				case "day":
					$value = World::TIME_DAY;
					break;
				case "noon":
					$value = World::TIME_NOON;
					break;
				case "sunset":
					$value = World::TIME_SUNSET;
					break;
				case "night":
					$value = World::TIME_NIGHT;
					break;
				case "midnight":
					$value = World::TIME_MIDNIGHT;
					break;
				case "sunrise":
					$value = World::TIME_SUNRISE;
					break;
				default:
					$value = $this->getInteger($sender, $args[1], 0);
					break;
			}

			foreach ($sender->getServer()->getWorldManager()->getWorlds() as $world) {
				$world->setTime($value);
			}
			Command::broadcastCommandMessage($sender, KnownTranslationFactory::commands_time_set((string) $value));
		} elseif ($args[0] === "add") {
			if (!$this->testPermission($sender, DefaultPermissionNames::COMMAND_TIME_ADD)) {
				return true;
			}

			$value = $this->getInteger($sender, $args[1], 0);
			foreach ($sender->getServer()->getWorldManager()->getWorlds() as $world) {
				$world->setTime($world->getTime() + $value);
			}
			Command::broadcastCommandMessage($sender, KnownTranslationFactory::commands_time_added((string) $value));
		} else {
			throw new InvalidCommandSyntaxException();
		}

		return true;
	}
}
