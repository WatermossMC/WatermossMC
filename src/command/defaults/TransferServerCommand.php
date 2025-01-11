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
use watermossmc\permission\DefaultPermissionNames;
use watermossmc\player\Player;

use function count;

class TransferServerCommand extends VanillaCommand
{
	public function __construct()
	{
		parent::__construct(
			"transferserver",
			KnownTranslationFactory::watermossmc_command_transferserver_description(),
			KnownTranslationFactory::watermossmc_command_transferserver_usage()
		);
		$this->setPermission(DefaultPermissionNames::COMMAND_TRANSFERSERVER);
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args)
	{
		if (count($args) < 1) {
			throw new InvalidCommandSyntaxException();
		} elseif (!($sender instanceof Player)) {
			$sender->sendMessage("This command must be executed as a player");

			return false;
		}

		$sender->transfer($args[0], (int) ($args[1] ?? 19132));

		return true;
	}
}
