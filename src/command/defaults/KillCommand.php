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
use watermossmc\event\entity\EntityDamageEvent;
use watermossmc\lang\KnownTranslationFactory;
use watermossmc\permission\DefaultPermissionNames;

use function count;

class KillCommand extends VanillaCommand
{
	public function __construct()
	{
		parent::__construct(
			"kill",
			KnownTranslationFactory::watermossmc_command_kill_description(),
			KnownTranslationFactory::watermossmc_command_kill_usage(),
			["suicide"]
		);
		$this->setPermissions([DefaultPermissionNames::COMMAND_KILL_SELF, DefaultPermissionNames::COMMAND_KILL_OTHER]);
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args)
	{
		if (count($args) >= 2) {
			throw new InvalidCommandSyntaxException();
		}

		$player = $this->fetchPermittedPlayerTarget($sender, $args[0] ?? null, DefaultPermissionNames::COMMAND_KILL_SELF, DefaultPermissionNames::COMMAND_KILL_OTHER);
		if ($player === null) {
			return true;
		}

		$player->attack(new EntityDamageEvent($player, EntityDamageEvent::CAUSE_SUICIDE, $player->getHealth()));
		if ($player === $sender) {
			$sender->sendMessage(KnownTranslationFactory::commands_kill_successful($sender->getName()));
		} else {
			Command::broadcastCommandMessage($sender, KnownTranslationFactory::commands_kill_successful($player->getName()));
		}

		return true;
	}
}
