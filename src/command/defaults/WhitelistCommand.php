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
use watermossmc\Server;
use watermossmc\ServerProperties;

use function count;
use function implode;
use function sort;
use function strtolower;

use const SORT_STRING;

class WhitelistCommand extends VanillaCommand
{
	public function __construct()
	{
		parent::__construct(
			"whitelist",
			KnownTranslationFactory::watermossmc_command_whitelist_description(),
			KnownTranslationFactory::commands_whitelist_usage()
		);
		$this->setPermissions([
			DefaultPermissionNames::COMMAND_WHITELIST_RELOAD,
			DefaultPermissionNames::COMMAND_WHITELIST_ENABLE,
			DefaultPermissionNames::COMMAND_WHITELIST_DISABLE,
			DefaultPermissionNames::COMMAND_WHITELIST_LIST,
			DefaultPermissionNames::COMMAND_WHITELIST_ADD,
			DefaultPermissionNames::COMMAND_WHITELIST_REMOVE
		]);
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args)
	{
		if (count($args) === 1) {
			switch (strtolower($args[0])) {
				case "reload":
					if ($this->testPermission($sender, DefaultPermissionNames::COMMAND_WHITELIST_RELOAD)) {
						$server = $sender->getServer();
						$server->getWhitelisted()->reload();
						if ($server->hasWhitelist()) {
							$this->kickNonWhitelistedPlayers($server);
						}
						Command::broadcastCommandMessage($sender, KnownTranslationFactory::commands_whitelist_reloaded());
					}

					return true;
				case "on":
					if ($this->testPermission($sender, DefaultPermissionNames::COMMAND_WHITELIST_ENABLE)) {
						$server = $sender->getServer();
						$server->getConfigGroup()->setConfigBool(ServerProperties::WHITELIST, true);
						$this->kickNonWhitelistedPlayers($server);
						Command::broadcastCommandMessage($sender, KnownTranslationFactory::commands_whitelist_enabled());
					}

					return true;
				case "off":
					if ($this->testPermission($sender, DefaultPermissionNames::COMMAND_WHITELIST_DISABLE)) {
						$sender->getServer()->getConfigGroup()->setConfigBool(ServerProperties::WHITELIST, false);
						Command::broadcastCommandMessage($sender, KnownTranslationFactory::commands_whitelist_disabled());
					}

					return true;
				case "list":
					if ($this->testPermission($sender, DefaultPermissionNames::COMMAND_WHITELIST_LIST)) {
						$entries = $sender->getServer()->getWhitelisted()->getAll(true);
						sort($entries, SORT_STRING);
						$result = implode(", ", $entries);
						$count = (string) count($entries);

						$sender->sendMessage(KnownTranslationFactory::commands_whitelist_list($count, $count));
						$sender->sendMessage($result);
					}

					return true;

				case "add":
					$sender->sendMessage(KnownTranslationFactory::commands_generic_usage(KnownTranslationFactory::commands_whitelist_add_usage()));
					return true;

				case "remove":
					$sender->sendMessage(KnownTranslationFactory::commands_generic_usage(KnownTranslationFactory::commands_whitelist_remove_usage()));
					return true;
			}
		} elseif (count($args) === 2) {
			if (!Player::isValidUserName($args[1])) {
				throw new InvalidCommandSyntaxException();
			}
			switch (strtolower($args[0])) {
				case "add":
					if ($this->testPermission($sender, DefaultPermissionNames::COMMAND_WHITELIST_ADD)) {
						$sender->getServer()->addWhitelist($args[1]);
						Command::broadcastCommandMessage($sender, KnownTranslationFactory::commands_whitelist_add_success($args[1]));
					}

					return true;
				case "remove":
					if ($this->testPermission($sender, DefaultPermissionNames::COMMAND_WHITELIST_REMOVE)) {
						$server = $sender->getServer();
						$server->removeWhitelist($args[1]);
						if (!$server->isWhitelisted($args[1])) {
							$server->getPlayerExact($args[1])?->kick(KnownTranslationFactory::watermossmc_disconnect_kick(KnownTranslationFactory::watermossmc_disconnect_whitelisted()));
						}
						Command::broadcastCommandMessage($sender, KnownTranslationFactory::commands_whitelist_remove_success($args[1]));
					}

					return true;
			}
		}

		throw new InvalidCommandSyntaxException();
	}

	private function kickNonWhitelistedPlayers(Server $server) : void
	{
		$message = KnownTranslationFactory::watermossmc_disconnect_kick(KnownTranslationFactory::watermossmc_disconnect_whitelisted());
		foreach ($server->getOnlinePlayers() as $player) {
			if (!$server->isWhitelisted($player->getName())) {
				$player->kick($message);
			}
		}
	}
}
