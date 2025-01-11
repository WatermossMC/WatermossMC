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
use watermossmc\player\Player;
use watermossmc\utils\TextFormat;

use function count;
use function implode;

class MeCommand extends VanillaCommand
{
	public function __construct()
	{
		parent::__construct(
			"me",
			KnownTranslationFactory::watermossmc_command_me_description(),
			KnownTranslationFactory::commands_me_usage()
		);
		$this->setPermission(DefaultPermissionNames::COMMAND_ME);
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args)
	{
		if (count($args) === 0) {
			throw new InvalidCommandSyntaxException();
		}

		$sender->getServer()->broadcastMessage(KnownTranslationFactory::chat_type_emote($sender instanceof Player ? $sender->getDisplayName() : $sender->getName(), TextFormat::RESET . implode(" ", $args)));

		return true;
	}
}
