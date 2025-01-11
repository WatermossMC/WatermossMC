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
use watermossmc\math\Vector3;
use watermossmc\permission\DefaultPermissionNames;
use watermossmc\player\Player;
use watermossmc\utils\TextFormat;
use watermossmc\world\World;

use function count;

class SetWorldSpawnCommand extends VanillaCommand
{
	public function __construct()
	{
		parent::__construct(
			"setworldspawn",
			KnownTranslationFactory::watermossmc_command_setworldspawn_description(),
			KnownTranslationFactory::commands_setworldspawn_usage()
		);
		$this->setPermission(DefaultPermissionNames::COMMAND_SETWORLDSPAWN);
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args)
	{
		if (count($args) === 0) {
			if ($sender instanceof Player) {
				$location = $sender->getPosition();
				$world = $location->getWorld();
				$pos = $location->asVector3()->floor();
			} else {
				$sender->sendMessage(TextFormat::RED . "You can only perform this command as a player");

				return true;
			}
		} elseif (count($args) === 3) {
			if ($sender instanceof Player) {
				$base = $sender->getPosition();
				$world = $base->getWorld();
			} else {
				$base = new Vector3(0.0, 0.0, 0.0);
				$world = $sender->getServer()->getWorldManager()->getDefaultWorld();
			}
			$pos = (new Vector3(
				$this->getRelativeDouble($base->x, $sender, $args[0]),
				$this->getRelativeDouble($base->y, $sender, $args[1], World::Y_MIN, World::Y_MAX),
				$this->getRelativeDouble($base->z, $sender, $args[2]),
			))->floor();
		} else {
			throw new InvalidCommandSyntaxException();
		}

		$world->setSpawnLocation($pos);

		Command::broadcastCommandMessage($sender, KnownTranslationFactory::commands_setworldspawn_success((string) $pos->x, (string) $pos->y, (string) $pos->z));

		return true;
	}
}
