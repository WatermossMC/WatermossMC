<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.watermossmc.net/
 *
 *
 */

declare(strict_types=1);

/*
 * This file is part of the WatermossMC
 * (c) 2025 WatermossMC <gameplaytebakgambard>
 *
 * @License Apache 2.0
 */

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
