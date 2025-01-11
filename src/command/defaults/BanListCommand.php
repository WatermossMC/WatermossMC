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
use watermossmc\permission\BanEntry;
use watermossmc\permission\DefaultPermissionNames;

use function array_map;
use function count;
use function implode;
use function sort;
use function strtolower;

use const SORT_STRING;

class BanListCommand extends VanillaCommand
{
	public function __construct()
	{
		parent::__construct(
			"banlist",
			KnownTranslationFactory::watermossmc_command_banlist_description(),
			KnownTranslationFactory::commands_banlist_usage()
		);
		$this->setPermission(DefaultPermissionNames::COMMAND_BAN_LIST);
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args)
	{
		if (isset($args[0])) {
			$args[0] = strtolower($args[0]);
			if ($args[0] === "ips") {
				$list = $sender->getServer()->getIPBans();
			} elseif ($args[0] === "players") {
				$list = $sender->getServer()->getNameBans();
			} else {
				throw new InvalidCommandSyntaxException();
			}
		} else {
			$list = $sender->getServer()->getNameBans();
			$args[0] = "players";
		}

		$list = array_map(function (BanEntry $entry) : string {
			return $entry->getName();
		}, $list->getEntries());
		sort($list, SORT_STRING);
		$message = implode(", ", $list);

		if ($args[0] === "ips") {
			$sender->sendMessage(KnownTranslationFactory::commands_banlist_ips((string) count($list)));
		} else {
			$sender->sendMessage(KnownTranslationFactory::commands_banlist_players((string) count($list)));
		}

		$sender->sendMessage($message);

		return true;
	}
}
