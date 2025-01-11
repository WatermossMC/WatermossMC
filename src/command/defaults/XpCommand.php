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
use watermossmc\entity\Attribute;
use watermossmc\lang\KnownTranslationFactory;
use watermossmc\permission\DefaultPermissionNames;
use watermossmc\utils\AssumptionFailedError;
use watermossmc\utils\Limits;
use watermossmc\utils\TextFormat;

use function abs;
use function count;
use function str_ends_with;
use function substr;

class XpCommand extends VanillaCommand
{
	public function __construct()
	{
		parent::__construct(
			"xp",
			KnownTranslationFactory::watermossmc_command_xp_description(),
			KnownTranslationFactory::watermossmc_command_xp_usage()
		);
		$this->setPermissions([
			DefaultPermissionNames::COMMAND_XP_SELF,
			DefaultPermissionNames::COMMAND_XP_OTHER
		]);
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args)
	{
		if (count($args) < 1) {
			throw new InvalidCommandSyntaxException();
		}

		$player = $this->fetchPermittedPlayerTarget($sender, $args[1] ?? null, DefaultPermissionNames::COMMAND_XP_SELF, DefaultPermissionNames::COMMAND_XP_OTHER);
		if ($player === null) {
			return true;
		}

		$xpManager = $player->getXpManager();
		if (str_ends_with($args[0], "L")) {
			$xpLevelAttr = $player->getAttributeMap()->get(Attribute::EXPERIENCE_LEVEL) ?? throw new AssumptionFailedError();
			$maxXpLevel = (int) $xpLevelAttr->getMaxValue();
			$currentXpLevel = $xpManager->getXpLevel();
			$xpLevels = $this->getInteger($sender, substr($args[0], 0, -1), -$currentXpLevel, $maxXpLevel - $currentXpLevel);
			if ($xpLevels >= 0) {
				$xpManager->addXpLevels($xpLevels, false);
				$sender->sendMessage(KnownTranslationFactory::commands_xp_success_levels((string) $xpLevels, $player->getName()));
			} else {
				$xpLevels = abs($xpLevels);
				$xpManager->subtractXpLevels($xpLevels);
				$sender->sendMessage(KnownTranslationFactory::commands_xp_success_negative_levels((string) $xpLevels, $player->getName()));
			}
		} else {
			$xp = $this->getInteger($sender, $args[0], max: Limits::INT32_MAX);
			if ($xp < 0) {
				$sender->sendMessage(KnownTranslationFactory::commands_xp_failure_widthdrawXp()->prefix(TextFormat::RED));
			} else {
				$xpManager->addXp($xp, false);
				$sender->sendMessage(KnownTranslationFactory::commands_xp_success((string) $xp, $player->getName()));
			}
		}

		return true;
	}
}
