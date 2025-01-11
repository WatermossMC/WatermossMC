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

class SaveOnCommand extends VanillaCommand
{
	public function __construct()
	{
		parent::__construct(
			"save-on",
			KnownTranslationFactory::watermossmc_command_saveon_description()
		);
		$this->setPermission(DefaultPermissionNames::COMMAND_SAVE_ENABLE);
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args)
	{
		$sender->getServer()->getWorldManager()->setAutoSave(true);

		Command::broadcastCommandMessage($sender, KnownTranslationFactory::commands_save_enabled());

		return true;
	}
}
