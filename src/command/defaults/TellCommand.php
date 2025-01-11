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
use watermossmc\utils\TextFormat;

use function array_shift;
use function count;
use function implode;

class TellCommand extends VanillaCommand
{
	public function __construct()
	{
		parent::__construct(
			"tell",
			KnownTranslationFactory::watermossmc_command_tell_description(),
			KnownTranslationFactory::commands_message_usage(),
			["w", "msg"]
		);
		$this->setPermission(DefaultPermissionNames::COMMAND_TELL);
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args)
	{
		if (count($args) < 2) {
			throw new InvalidCommandSyntaxException();
		}

		$player = $sender->getServer()->getPlayerByPrefix(array_shift($args));

		if ($player === $sender) {
			$sender->sendMessage(KnownTranslationFactory::commands_message_sameTarget()->prefix(TextFormat::RED));
			return true;
		}

		if ($player instanceof Player) {
			$message = implode(" ", $args);
			$sender->sendMessage(KnownTranslationFactory::commands_message_display_outgoing($player->getDisplayName(), $message)->prefix(TextFormat::GRAY . TextFormat::ITALIC));
			$name = $sender instanceof Player ? $sender->getDisplayName() : $sender->getName();
			$player->sendMessage(KnownTranslationFactory::commands_message_display_incoming($name, $message)->prefix(TextFormat::GRAY . TextFormat::ITALIC));
			Command::broadcastCommandMessage($sender, KnownTranslationFactory::commands_message_display_outgoing($player->getDisplayName(), $message), false);
		} else {
			$sender->sendMessage(KnownTranslationFactory::commands_generic_player_notFound());
		}

		return true;
	}
}
