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
