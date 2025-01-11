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

class SeedCommand extends VanillaCommand
{
	public function __construct()
	{
		parent::__construct(
			"seed",
			KnownTranslationFactory::watermossmc_command_seed_description()
		);
		$this->setPermission(DefaultPermissionNames::COMMAND_SEED);
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args)
	{
		if ($sender instanceof Player) {
			$seed = $sender->getPosition()->getWorld()->getSeed();
		} else {
			$seed = $sender->getServer()->getWorldManager()->getDefaultWorld()->getSeed();
		}
		$sender->sendMessage(KnownTranslationFactory::commands_seed_success((string) $seed));

		return true;
	}
}
