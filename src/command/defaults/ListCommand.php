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
use watermossmc\lang\KnownTranslationFactory;
use watermossmc\permission\DefaultPermissionNames;
use watermossmc\player\Player;

use function array_filter;
use function array_map;
use function count;
use function implode;
use function sort;

use const SORT_STRING;

class ListCommand extends VanillaCommand
{
	public function __construct()
	{
		parent::__construct(
			"list",
			KnownTranslationFactory::watermossmc_command_list_description()
		);
		$this->setPermission(DefaultPermissionNames::COMMAND_LIST);
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args)
	{
		$playerNames = array_map(function (Player $player) : string {
			return $player->getName();
		}, array_filter($sender->getServer()->getOnlinePlayers(), function (Player $player) use ($sender) : bool {
			return !($sender instanceof Player) || $sender->canSee($player);
		}));
		sort($playerNames, SORT_STRING);

		$sender->sendMessage(KnownTranslationFactory::commands_players_list((string) count($playerNames), (string) $sender->getServer()->getMaxPlayers()));
		$sender->sendMessage(implode(", ", $playerNames));

		return true;
	}
}
