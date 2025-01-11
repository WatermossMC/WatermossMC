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

use function array_slice;
use function count;
use function implode;

class TitleCommand extends VanillaCommand
{
	public function __construct()
	{
		parent::__construct(
			"title",
			KnownTranslationFactory::watermossmc_command_title_description(),
			KnownTranslationFactory::commands_title_usage()
		);
		$this->setPermissions([
			DefaultPermissionNames::COMMAND_TITLE_SELF,
			DefaultPermissionNames::COMMAND_TITLE_OTHER
		]);
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args)
	{
		if (count($args) < 2) {
			throw new InvalidCommandSyntaxException();
		}

		$player = $this->fetchPermittedPlayerTarget($sender, $args[0], DefaultPermissionNames::COMMAND_TITLE_SELF, DefaultPermissionNames::COMMAND_TITLE_OTHER);
		if ($player === null) {
			return true;
		}

		switch ($args[1]) {
			case "clear":
				$player->removeTitles();
				break;
			case "reset":
				$player->resetTitles();
				break;
			case "title":
				if (count($args) < 3) {
					throw new InvalidCommandSyntaxException();
				}

				$player->sendTitle(implode(" ", array_slice($args, 2)));
				break;
			case "subtitle":
				if (count($args) < 3) {
					throw new InvalidCommandSyntaxException();
				}

				$player->sendSubTitle(implode(" ", array_slice($args, 2)));
				break;
			case "actionbar":
				if (count($args) < 3) {
					throw new InvalidCommandSyntaxException();
				}

				$player->sendActionBarMessage(implode(" ", array_slice($args, 2)));
				break;
			case "times":
				if (count($args) < 5) {
					throw new InvalidCommandSyntaxException();
				}

				$player->setTitleDuration($this->getInteger($sender, $args[2]), $this->getInteger($sender, $args[3]), $this->getInteger($sender, $args[4]));
				break;
			default:
				throw new InvalidCommandSyntaxException();
		}

		$sender->sendMessage(KnownTranslationFactory::commands_title_success());

		return true;
	}
}
