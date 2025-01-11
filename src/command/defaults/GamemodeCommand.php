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
use watermossmc\player\GameMode;

use function count;

class GamemodeCommand extends VanillaCommand
{
	public function __construct()
	{
		parent::__construct(
			"gamemode",
			KnownTranslationFactory::watermossmc_command_gamemode_description(),
			KnownTranslationFactory::commands_gamemode_usage()
		);
		$this->setPermissions([
			DefaultPermissionNames::COMMAND_GAMEMODE_SELF,
			DefaultPermissionNames::COMMAND_GAMEMODE_OTHER
		]);
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args)
	{
		if (count($args) === 0) {
			throw new InvalidCommandSyntaxException();
		}

		$gameMode = GameMode::fromString($args[0]);
		if ($gameMode === null) {
			$sender->sendMessage(KnownTranslationFactory::watermossmc_command_gamemode_unknown($args[0]));
			return true;
		}

		$target = $this->fetchPermittedPlayerTarget($sender, $args[1] ?? null, DefaultPermissionNames::COMMAND_GAMEMODE_SELF, DefaultPermissionNames::COMMAND_GAMEMODE_OTHER);
		if ($target === null) {
			return true;
		}

		if ($target->getGamemode() === $gameMode) {
			$sender->sendMessage(KnownTranslationFactory::watermossmc_command_gamemode_failure($target->getName()));
			return true;
		}

		$target->setGamemode($gameMode);
		if ($gameMode !== $target->getGamemode()) {
			$sender->sendMessage(KnownTranslationFactory::watermossmc_command_gamemode_failure($target->getName()));
		} else {
			if ($target === $sender) {
				Command::broadcastCommandMessage($sender, KnownTranslationFactory::commands_gamemode_success_self($gameMode->getTranslatableName()));
			} else {
				$target->sendMessage(KnownTranslationFactory::gameMode_changed($gameMode->getTranslatableName()));
				Command::broadcastCommandMessage($sender, KnownTranslationFactory::commands_gamemode_success_other($gameMode->getTranslatableName(), $target->getName()));
			}
		}

		return true;
	}
}
