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
use watermossmc\plugin\Plugin;
use watermossmc\utils\TextFormat;

use function array_map;
use function count;
use function implode;
use function sort;

use const SORT_STRING;

class PluginsCommand extends VanillaCommand
{
	public function __construct()
	{
		parent::__construct(
			"plugins",
			KnownTranslationFactory::watermossmc_command_plugins_description(),
			null,
			["pl"]
		);
		$this->setPermission(DefaultPermissionNames::COMMAND_PLUGINS);
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args)
	{
		$list = array_map(function (Plugin $plugin) : string {
			return ($plugin->isEnabled() ? TextFormat::GREEN : TextFormat::RED) . $plugin->getDescription()->getFullName();
		}, $sender->getServer()->getPluginManager()->getPlugins());
		sort($list, SORT_STRING);

		$sender->sendMessage(KnownTranslationFactory::watermossmc_command_plugins_success((string) count($list), implode(TextFormat::RESET . ", ", $list)));
		return true;
	}
}
