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
use watermossmc\lang\Translatable;
use watermossmc\permission\DefaultPermissionNames;
use watermossmc\utils\TextFormat;

use function array_chunk;
use function array_pop;
use function count;
use function explode;
use function implode;
use function is_numeric;
use function ksort;
use function min;
use function sort;
use function strtolower;

use const SORT_FLAG_CASE;
use const SORT_NATURAL;

class HelpCommand extends VanillaCommand
{
	public function __construct()
	{
		parent::__construct(
			"help",
			KnownTranslationFactory::watermossmc_command_help_description(),
			KnownTranslationFactory::commands_help_usage(),
			["?"]
		);
		$this->setPermission(DefaultPermissionNames::COMMAND_HELP);
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args)
	{
		if (count($args) === 0) {
			$commandName = "";
			$pageNumber = 1;
		} elseif (is_numeric($args[count($args) - 1])) {
			$pageNumber = (int) array_pop($args);
			if ($pageNumber <= 0) {
				$pageNumber = 1;
			}
			$commandName = implode(" ", $args);
		} else {
			$commandName = implode(" ", $args);
			$pageNumber = 1;
		}

		$pageHeight = $sender->getScreenLineHeight();

		if ($commandName === "") {
			$commands = [];
			foreach ($sender->getServer()->getCommandMap()->getCommands() as $command) {
				if ($command->testPermissionSilent($sender)) {
					$commands[$command->getLabel()] = $command;
				}
			}
			ksort($commands, SORT_NATURAL | SORT_FLAG_CASE);
			$commands = array_chunk($commands, $pageHeight);
			$pageNumber = min(count($commands), $pageNumber);
			if ($pageNumber < 1) {
				$pageNumber = 1;
			}
			$sender->sendMessage(KnownTranslationFactory::commands_help_header((string) $pageNumber, (string) count($commands)));
			$lang = $sender->getLanguage();
			if (isset($commands[$pageNumber - 1])) {
				foreach ($commands[$pageNumber - 1] as $command) {
					$description = $command->getDescription();
					$descriptionString = $description instanceof Translatable ? $lang->translate($description) : $description;
					$sender->sendMessage(TextFormat::DARK_GREEN . "/" . $command->getLabel() . ": " . TextFormat::RESET . $descriptionString);
				}
			}

			return true;
		} else {
			if (($cmd = $sender->getServer()->getCommandMap()->getCommand(strtolower($commandName))) instanceof Command) {
				if ($cmd->testPermissionSilent($sender)) {
					$lang = $sender->getLanguage();
					$description = $cmd->getDescription();
					$descriptionString = $description instanceof Translatable ? $lang->translate($description) : $description;
					$sender->sendMessage(KnownTranslationFactory::watermossmc_command_help_specificCommand_header($cmd->getLabel())
						->format(TextFormat::YELLOW . "--------- " . TextFormat::RESET, TextFormat::YELLOW . " ---------"));
					$sender->sendMessage(KnownTranslationFactory::watermossmc_command_help_specificCommand_description(TextFormat::RESET . $descriptionString)
						->prefix(TextFormat::GOLD));

					$usage = $cmd->getUsage();
					$usageString = $usage instanceof Translatable ? $lang->translate($usage) : $usage;
					$sender->sendMessage(KnownTranslationFactory::watermossmc_command_help_specificCommand_usage(TextFormat::RESET . implode("\n" . TextFormat::RESET, explode("\n", $usageString)))
						->prefix(TextFormat::GOLD));

					$aliases = $cmd->getAliases();
					sort($aliases, SORT_NATURAL);
					$sender->sendMessage(KnownTranslationFactory::watermossmc_command_help_specificCommand_aliases(TextFormat::RESET . implode(", ", $aliases))
						->prefix(TextFormat::GOLD));

					return true;
				}
			}
			$sender->sendMessage(KnownTranslationFactory::watermossmc_command_notFound($commandName, "/help")->prefix(TextFormat::RED));

			return true;
		}
	}
}
