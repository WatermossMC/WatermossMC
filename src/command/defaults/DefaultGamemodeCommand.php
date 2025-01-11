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

use watermossmc\command\CommandSender;
use watermossmc\command\utils\InvalidCommandSyntaxException;
use watermossmc\lang\KnownTranslationFactory;
use watermossmc\permission\DefaultPermissionNames;
use watermossmc\player\GameMode;
use watermossmc\ServerProperties;

use function count;

class DefaultGamemodeCommand extends VanillaCommand
{
	public function __construct()
	{
		parent::__construct(
			"defaultgamemode",
			KnownTranslationFactory::watermossmc_command_defaultgamemode_description(),
			KnownTranslationFactory::commands_defaultgamemode_usage()
		);
		$this->setPermission(DefaultPermissionNames::COMMAND_DEFAULTGAMEMODE);
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

		//TODO: this probably shouldn't use the enum name directly
		$sender->getServer()->getConfigGroup()->setConfigString(ServerProperties::GAME_MODE, $gameMode->name);
		$sender->sendMessage(KnownTranslationFactory::commands_defaultgamemode_success($gameMode->getTranslatableName()));
		return true;
	}
}
