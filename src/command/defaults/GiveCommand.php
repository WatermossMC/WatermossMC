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
use watermossmc\item\LegacyStringToItemParser;
use watermossmc\item\LegacyStringToItemParserException;
use watermossmc\item\StringToItemParser;
use watermossmc\lang\KnownTranslationFactory;
use watermossmc\nbt\JsonNbtParser;
use watermossmc\nbt\NbtDataException;
use watermossmc\nbt\NbtException;
use watermossmc\permission\DefaultPermissionNames;
use watermossmc\utils\TextFormat;

use function array_slice;
use function count;
use function implode;

class GiveCommand extends VanillaCommand
{
	public function __construct()
	{
		parent::__construct(
			"give",
			KnownTranslationFactory::watermossmc_command_give_description(),
			KnownTranslationFactory::watermossmc_command_give_usage()
		);
		$this->setPermissions([
			DefaultPermissionNames::COMMAND_GIVE_SELF,
			DefaultPermissionNames::COMMAND_GIVE_OTHER
		]);
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args)
	{
		if (count($args) < 2) {
			throw new InvalidCommandSyntaxException();
		}

		$player = $this->fetchPermittedPlayerTarget($sender, $args[0], DefaultPermissionNames::COMMAND_GIVE_SELF, DefaultPermissionNames::COMMAND_GIVE_OTHER);
		if ($player === null) {
			return true;
		}

		try {
			$item = StringToItemParser::getInstance()->parse($args[1]) ?? LegacyStringToItemParser::getInstance()->parse($args[1]);
		} catch (LegacyStringToItemParserException $e) {
			$sender->sendMessage(KnownTranslationFactory::commands_give_item_notFound($args[1])->prefix(TextFormat::RED));
			return true;
		}

		if (!isset($args[2])) {
			$item->setCount($item->getMaxStackSize());
		} else {
			$count = $this->getBoundedInt($sender, $args[2], 1, 32767);
			if ($count === null) {
				return true;
			}
			$item->setCount($count);
		}

		if (isset($args[3])) {
			$data = implode(" ", array_slice($args, 3));
			try {
				$tags = JsonNbtParser::parseJson($data);
			} catch (NbtDataException $e) {
				$sender->sendMessage(KnownTranslationFactory::commands_give_tagError($e->getMessage()));
				return true;
			}

			try {
				$item->setNamedTag($tags);
			} catch (NbtException $e) {
				$sender->sendMessage(KnownTranslationFactory::commands_give_tagError($e->getMessage()));
				return true;
			}
		}

		//TODO: overflow
		$player->getInventory()->addItem($item);

		Command::broadcastCommandMessage($sender, KnownTranslationFactory::commands_give_success(
			$item->getName() . " (" . $args[1] . ")",
			(string) $item->getCount(),
			$player->getName()
		));
		return true;
	}
}
