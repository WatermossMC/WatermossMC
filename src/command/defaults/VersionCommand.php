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
use watermossmc\network\mcpe\protocol\ProtocolInfo;
use watermossmc\permission\DefaultPermissionNames;
use watermossmc\plugin\Plugin;
use watermossmc\utils\TextFormat;
use watermossmc\utils\Utils;
use watermossmc\VersionInfo;

use function count;
use function implode;
use function sprintf;
use function stripos;
use function strtolower;

use const PHP_VERSION;

class VersionCommand extends VanillaCommand
{
	public function __construct()
	{
		parent::__construct(
			"version",
			KnownTranslationFactory::watermossmc_command_version_description(),
			KnownTranslationFactory::watermossmc_command_version_usage(),
			["ver", "about"]
		);
		$this->setPermission(DefaultPermissionNames::COMMAND_VERSION);
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args)
	{
		if (count($args) === 0) {
			$sender->sendMessage(KnownTranslationFactory::watermossmc_command_version_serverSoftwareName(
				TextFormat::GREEN . VersionInfo::NAME . TextFormat::RESET
			));
			$versionColor = VersionInfo::IS_DEVELOPMENT_BUILD ? TextFormat::YELLOW : TextFormat::GREEN;
			$sender->sendMessage(KnownTranslationFactory::watermossmc_command_version_serverSoftwareVersion(
				$versionColor . VersionInfo::VERSION()->getFullVersion() . TextFormat::RESET,
				TextFormat::GREEN . VersionInfo::GIT_HASH() . TextFormat::RESET
			));
			$sender->sendMessage(KnownTranslationFactory::watermossmc_command_version_minecraftVersion(
				TextFormat::GREEN . ProtocolInfo::MINECRAFT_VERSION_NETWORK . TextFormat::RESET,
				TextFormat::GREEN . ProtocolInfo::CURRENT_PROTOCOL . TextFormat::RESET
			));
			$sender->sendMessage(KnownTranslationFactory::watermossmc_command_version_phpVersion(TextFormat::GREEN . PHP_VERSION . TextFormat::RESET));

			$jitMode = Utils::getOpcacheJitMode();
			if ($jitMode !== null) {
				if ($jitMode !== 0) {
					$jitStatus = KnownTranslationFactory::watermossmc_command_version_phpJitEnabled(sprintf("CRTO: %d", $jitMode));
				} else {
					$jitStatus = KnownTranslationFactory::watermossmc_command_version_phpJitDisabled();
				}
			} else {
				$jitStatus = KnownTranslationFactory::watermossmc_command_version_phpJitNotSupported();
			}
			$sender->sendMessage(KnownTranslationFactory::watermossmc_command_version_phpJitStatus($jitStatus->format(TextFormat::GREEN, TextFormat::RESET)));
			$sender->sendMessage(KnownTranslationFactory::watermossmc_command_version_operatingSystem(TextFormat::GREEN . Utils::getOS() . TextFormat::RESET));
		} else {
			$pluginName = implode(" ", $args);
			$exactPlugin = $sender->getServer()->getPluginManager()->getPlugin($pluginName);

			if ($exactPlugin instanceof Plugin) {
				$this->describeToSender($exactPlugin, $sender);

				return true;
			}

			$found = false;
			$pluginName = strtolower($pluginName);
			foreach ($sender->getServer()->getPluginManager()->getPlugins() as $plugin) {
				if (stripos($plugin->getName(), $pluginName) !== false) {
					$this->describeToSender($plugin, $sender);
					$found = true;
				}
			}

			if (!$found) {
				$sender->sendMessage(KnownTranslationFactory::watermossmc_command_version_noSuchPlugin());
			}
		}

		return true;
	}

	private function describeToSender(Plugin $plugin, CommandSender $sender) : void
	{
		$desc = $plugin->getDescription();
		$sender->sendMessage(TextFormat::DARK_GREEN . $desc->getName() . TextFormat::RESET . " version " . TextFormat::DARK_GREEN . $desc->getVersion());

		if ($desc->getDescription() !== "") {
			$sender->sendMessage($desc->getDescription());
		}

		if ($desc->getWebsite() !== "") {
			$sender->sendMessage("Website: " . $desc->getWebsite());
		}

		if (count($authors = $desc->getAuthors()) > 0) {
			if (count($authors) === 1) {
				$sender->sendMessage("Author: " . implode(", ", $authors));
			} else {
				$sender->sendMessage("Authors: " . implode(", ", $authors));
			}
		}
	}
}
