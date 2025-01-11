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
use watermossmc\item\enchantment\EnchantingHelper;
use watermossmc\item\enchantment\EnchantmentInstance;
use watermossmc\item\enchantment\StringToEnchantmentParser;
use watermossmc\lang\KnownTranslationFactory;
use watermossmc\permission\DefaultPermissionNames;

use function count;

class EnchantCommand extends VanillaCommand
{
	public function __construct()
	{
		parent::__construct(
			"enchant",
			KnownTranslationFactory::watermossmc_command_enchant_description(),
			KnownTranslationFactory::commands_enchant_usage()
		);
		$this->setPermissions([
			DefaultPermissionNames::COMMAND_ENCHANT_SELF,
			DefaultPermissionNames::COMMAND_ENCHANT_OTHER
		]);
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args)
	{
		if (count($args) < 2) {
			throw new InvalidCommandSyntaxException();
		}

		$player = $this->fetchPermittedPlayerTarget($sender, $args[0], DefaultPermissionNames::COMMAND_ENCHANT_SELF, DefaultPermissionNames::COMMAND_ENCHANT_OTHER);
		if ($player === null) {
			return true;
		}

		$item = $player->getInventory()->getItemInHand();

		if ($item->isNull()) {
			$sender->sendMessage(KnownTranslationFactory::commands_enchant_noItem());
			return true;
		}

		$enchantment = StringToEnchantmentParser::getInstance()->parse($args[1]);
		if ($enchantment === null) {
			$sender->sendMessage(KnownTranslationFactory::commands_enchant_notFound($args[1]));
			return true;
		}

		$level = 1;
		if (isset($args[2])) {
			$level = $this->getBoundedInt($sender, $args[2], 1, $enchantment->getMaxLevel());
			if ($level === null) {
				return false;
			}
		}

		//this is necessary to deal with enchanted books, which are a different item type than regular books
		$enchantedItem = EnchantingHelper::enchantItem($item, [new EnchantmentInstance($enchantment, $level)]);
		$player->getInventory()->setItemInHand($enchantedItem);

		self::broadcastCommandMessage($sender, KnownTranslationFactory::commands_enchant_success($player->getName()));
		return true;
	}
}
