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
use watermossmc\lang\KnownTranslationFactory;
use watermossmc\permission\DefaultPermissionNames;

use function microtime;
use function round;

class SaveCommand extends VanillaCommand
{
	public function __construct()
	{
		parent::__construct(
			"save-all",
			KnownTranslationFactory::watermossmc_command_save_description()
		);
		$this->setPermission(DefaultPermissionNames::COMMAND_SAVE_PERFORM);
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args)
	{
		Command::broadcastCommandMessage($sender, KnownTranslationFactory::watermossmc_save_start());
		$start = microtime(true);

		foreach ($sender->getServer()->getOnlinePlayers() as $player) {
			$player->save();
		}

		foreach ($sender->getServer()->getWorldManager()->getWorlds() as $world) {
			$world->save(true);
		}

		Command::broadcastCommandMessage($sender, KnownTranslationFactory::watermossmc_save_success((string) round(microtime(true) - $start, 3)));

		return true;
	}
}
