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
use watermossmc\ServerProperties;
use watermossmc\world\World;

use function count;

class DifficultyCommand extends VanillaCommand
{
	public function __construct()
	{
		parent::__construct(
			"difficulty",
			KnownTranslationFactory::watermossmc_command_difficulty_description(),
			KnownTranslationFactory::commands_difficulty_usage()
		);
		$this->setPermission(DefaultPermissionNames::COMMAND_DIFFICULTY);
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args)
	{
		if (count($args) !== 1) {
			throw new InvalidCommandSyntaxException();
		}

		$difficulty = World::getDifficultyFromString($args[0]);

		if ($sender->getServer()->isHardcore()) {
			$difficulty = World::DIFFICULTY_HARD;
		}

		if ($difficulty !== -1) {
			$sender->getServer()->getConfigGroup()->setConfigInt(ServerProperties::DIFFICULTY, $difficulty);

			//TODO: add per-world support
			foreach ($sender->getServer()->getWorldManager()->getWorlds() as $world) {
				$world->setDifficulty($difficulty);
			}

			Command::broadcastCommandMessage($sender, KnownTranslationFactory::commands_difficulty_success((string) $difficulty));
		} else {
			throw new InvalidCommandSyntaxException();
		}

		return true;
	}
}
