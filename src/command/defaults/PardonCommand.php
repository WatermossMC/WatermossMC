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
use watermossmc\permission\DefaultPermissionNames;

use function count;

class PardonCommand extends VanillaCommand
{
	public function __construct()
	{
		parent::__construct(
			"pardon",
			KnownTranslationFactory::watermossmc_command_unban_player_description(),
			KnownTranslationFactory::commands_unban_usage(),
			["unban"]
		);
		$this->setPermission(DefaultPermissionNames::COMMAND_UNBAN_PLAYER);
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args)
	{
		if (count($args) !== 1) {
			throw new InvalidCommandSyntaxException();
		}

		$sender->getServer()->getNameBans()->remove($args[0]);

		Command::broadcastCommandMessage($sender, KnownTranslationFactory::commands_unban_success($args[0]));

		return true;
	}
}
