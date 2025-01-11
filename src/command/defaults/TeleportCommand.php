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
use watermossmc\entity\Location;
use watermossmc\lang\KnownTranslationFactory;
use watermossmc\permission\DefaultPermissionNames;
use watermossmc\player\Player;
use watermossmc\utils\AssumptionFailedError;
use watermossmc\utils\TextFormat;
use watermossmc\world\World;

use function array_shift;
use function count;
use function round;

class TeleportCommand extends VanillaCommand
{
	public function __construct()
	{
		parent::__construct(
			"tp",
			KnownTranslationFactory::watermossmc_command_tp_description(),
			KnownTranslationFactory::commands_tp_usage(),
			["teleport"]
		);
		$this->setPermissions([
			DefaultPermissionNames::COMMAND_TELEPORT_SELF,
			DefaultPermissionNames::COMMAND_TELEPORT_OTHER
		]);
	}

	private function findPlayer(CommandSender $sender, string $playerName) : ?Player
	{
		$subject = $sender->getServer()->getPlayerByPrefix($playerName);
		if ($subject === null) {
			$sender->sendMessage(TextFormat::RED . "Can't find player " . $playerName);
			return null;
		}
		return $subject;
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args)
	{
		switch (count($args)) {
			case 1: // /tp targetPlayer
			case 3: // /tp x y z
			case 5: // /tp x y z yaw pitch - TODO: 5 args could be target x y z yaw :(
				$subjectName = null; //self
				break;
			case 2: // /tp player1 player2
			case 4: // /tp player1 x y z - TODO: 4 args could be x y z yaw :(
			case 6: // /tp player1 x y z yaw pitch
				$subjectName = array_shift($args);
				break;
			default:
				throw new InvalidCommandSyntaxException();
		}

		$subject = $this->fetchPermittedPlayerTarget($sender, $subjectName, DefaultPermissionNames::COMMAND_TELEPORT_SELF, DefaultPermissionNames::COMMAND_TELEPORT_OTHER);
		if ($subject === null) {
			return true;
		}

		switch (count($args)) {
			case 1:
				$targetPlayer = $this->findPlayer($sender, $args[0]);
				if ($targetPlayer === null) {
					return true;
				}

				$subject->teleport($targetPlayer->getLocation());
				Command::broadcastCommandMessage($sender, KnownTranslationFactory::commands_tp_success($subject->getName(), $targetPlayer->getName()));

				return true;
			case 3:
			case 5:
				$base = $subject->getLocation();
				if (count($args) === 5) {
					$yaw = (float) $args[3];
					$pitch = (float) $args[4];
				} else {
					$yaw = $base->yaw;
					$pitch = $base->pitch;
				}

				$x = $this->getRelativeDouble($base->x, $sender, $args[0]);
				$y = $this->getRelativeDouble($base->y, $sender, $args[1], World::Y_MIN, World::Y_MAX);
				$z = $this->getRelativeDouble($base->z, $sender, $args[2]);
				$targetLocation = new Location($x, $y, $z, $base->getWorld(), $yaw, $pitch);

				$subject->teleport($targetLocation);
				Command::broadcastCommandMessage($sender, KnownTranslationFactory::commands_tp_success_coordinates(
					$subject->getName(),
					(string) round($targetLocation->x, 2),
					(string) round($targetLocation->y, 2),
					(string) round($targetLocation->z, 2)
				));
				return true;
			default:
				throw new AssumptionFailedError("This branch should be unreachable (for now)");
		}
	}
}
